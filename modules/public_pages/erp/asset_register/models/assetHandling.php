<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class assetHandling
{

	protected $version = '$Revision: 1.14 $';
	
	static function depreciateAll(&$errors)
	{
		$assets = new AssetCollection(DataObjectFactory::Factory('Asset'));
		
		$sh = new SearchHandler($assets, false);
		$sh->addConstraint(new Constraint('disposal_date', 'is', 'NULL'));
		
		$assets->load($sh);
		
		if ($assets)
		{

			$progressbar = new progressBar('depreciation');
			
			$callback = function($asset, $key) use (&$errors)
			{
				assetHandling::depreciateAsset($asset, $errors);
				
				if (count($errors)>0)
				{
					return FALSE;
				}
				
			};
			
			if ($progressbar->process($assets, $callback)===FALSE)
			{
				$errors[] = 'Error running depreciation';
			}
		}
		else
		{
			$errors[]='Failed to load assets';
		}
		
	}

	static function depreciateOne($assetid, &$errors)
	{
		
		$asset = DataObjectFactory::Factory('Asset');
		
		if ($asset->load($assetid))
		{
			self::depreciateAsset($asset, $errors);
		}
		else
		{
			$errors[]='Failed to load asset';
		}
		
	}

	static function depreciateAsset($asset, &$errors)
	{
		
		$depn_td = $asset->depreciation($errors);
		
		if ($depn_td && count($errors) === 0 && (bcsub($depn_td, $asset->td_depn)) != 0)
		{
			
			$gltrans = GLTransaction::makeFromAsset($asset, bcsub($depn_td, $asset->td_depn), 'depreciation', $errors);
			
			if (count($errors) === 0 && GLTransaction::saveTransactions($gltrans, $errors))
			{
				
				if ($asset->bfwd_value > 0)
				{
					$depn_previousyear = $asset->purchase_price-$asset->bfwd_value;
				}
				else
				{
					$depn_previousyear = 0;
				}
				
				$asset->ty_depn = $depn_td-$depn_previousyear;
				$asset->td_depn = $depn_td;
				
				$asset->wd_value = $asset->purchase_price-$asset->td_depn;
				
				if (!$asset->save())
				{
					$errors[]='Failed to update asset depreciation';
				}
			}
		}
		
	}

	static function disposal($assetid, $data, &$errors)
	 {
		
	 	$asset = DataObjectFactory::Factory('Asset');
		
	 	if ($asset->load($assetid))
	 	{
			
	 		$asset->disposal_date	= $data['disposal_date'];
	 		$asset->disposal_value	= $data['disposal_value'];
	 		
	 		if ($asset->save())
	 		{
				unset($data['id']);
				
				$gltrans = array();
				
				$gltrans = GLTransaction::makeFromAsset($asset, $asset->purchase_price, 'disposal', $errors);
				
				if (count($errors) === 0 && count($gltrans) > 0)
				{
					$gltrans=array_merge($gltrans, GLTransaction::makeFromAsset($asset, $asset->td_depn, 'disposal-depreciation', $errors));
				}
				
				if (count($errors) === 0 && count($gltrans) > 0)
				{
					$data['armaster_id']		= $asset->id;
					$data['value']				= $data['disposal_value'];
					$data['transaction_date']	= $data['disposal_date'];
					$data['from_group_id']		= $asset->argroup_id;
					$data['from_location_id']	= $asset->arlocation_id;
					
					$artransaction = DataObjectFactory::Factory('ARTransaction');
					
					$artransaction = $artransaction->add($data, $artransaction->disposal(), $errors);
					
					$result = false;
					
					if ($artransaction && count($errors) === 0)
					{
						$result = $artransaction->save();
					}
					
					if ($result)
					{
						$gltrans = array_merge($gltrans, GLTransaction::makeFromAssetTransaction($artransaction, $asset, $errors));
					}
					
					if ($result === FALSE || count($errors) > 0 || GLTransaction::saveTransactions($gltrans, $errors) === FALSE)
					{
						$errors[] = 'Failed to create GL entry for asset disposal';
					}
				}
			}
			else
			{
				$errors[] = 'Failed to update asset disposal date';
			}
		}
		else
		{
			$errors[] = 'Failed to load asset';
		}
	}

	static function save($data, &$errors)
	{
		$period = DataObjectFactory::Factory('GLPeriod');
		
		if ($period->loadPeriod($data['purchase_date']))
		{
			$data['purchase_period_id'] = $period->id;
		}
		else
		{
			$errors[] = 'No period defined for this purchase date';
			return;
		}
		
		$data['wd_value'] = $data['purchase_price'];
		
		$asset=DataObject::Factory($data, $errors, 'Asset');
		
		if(count($errors) === 0 && $asset->save())
		{
			
			$data['armaster_id']		= $asset->id;
			$data['transaction_date']	= $data['purchase_date'];
			$data['value']				= $data['purchase_price'];
			$data['to_group_id']		= $data['argroup_id'];
			$data['to_location_id']		= $data['arlocation_id'];
			
			$artransaction = DataObjectFactory::Factory('ARTransaction');
			
			$artransaction = $artransaction->add($data, $artransaction->addition(), $errors);
			
			$result = false;
			
			if ($artransaction && count($errors) === 0)
			{
				$result = $artransaction->save();
			}
			
			if ($result)
			{
				$gltrans = GLTransaction::makeFromAssetTransaction($artransaction, $asset, $errors);
			}
			
			if ($result === FALSE || count($errors) > 0 || GLTransaction::saveTransactions($gltrans, $errors) === FALSE)
			{
				$errors[] = 'Failed to create new Asset';
			}
		}
	}
	
	static function yearEnd (&$errors)
	{
		
		$assets = new AssetCollection(DataObjectFactory::Factory('Asset'));
		
		$sh = new SearchHandler($assets, false);
		
		$assets->load($sh);
		
		if ($assets->count() > 0)
		{
		
			$db = DB::Instance();
			$db->StartTrans();
			
			foreach ($assets as $asset)
			{
				if (is_null($asset->disposal_date))
				{
					$asset->bfwd_value = $asset->wd_value;
					
					$asset->ty_depn = 0;
					
					if (!$asset->save())
					{
						$errors[] = 'Failed to update asset '.$asset->code;
					}
				}
				
				if (count($errors)>0)
				{
					break;
				}
			}
			
			if (count($errors) > 0)
			{
				$db->FailTrans();
			}

			$db->CompleteTrans();
// TODO : display warning if no assets to depreciate			
//		} else {
//			$errors[]='Failed to load assets';
		}
		
	}

}

// End of assetHandling
