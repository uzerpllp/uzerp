<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class File extends DataObject
{
	
	protected $version='$Revision: 1.5 $';
	
	public $tmp_name;
	
	public $path;
	
	public function __construct($path='')
	{
		$this->path=$path;
		
		parent::__construct('file');
		
		$this->idField='id';
		
		$this->getField('size')->setFormatter(new FilesizeFormatter());
	}
	
	public function delete($id = null, $errors = array(), $archive = FALSE, $archive_table = null, $archive_schema = null)
	{
		if (!$this->isLoaded())
		{
			if (empty($id))
			{
				return false;
			}
			else
			{
				$this->load($id);
			}
		}
		
		$db = DB::Instance();
		
		$db->StartTrans();
		
		// Delete the associated file blob
		$result = $db->BlobDelete($this->file);
		
		if ($result)
		{
			// Now delete the file entry
			$result = parent::delete($id, $errors, $archive, $archive_table, $archive_schema);
		}
		else
		{
			$errors[] = 'Error deleting file : '.$db->ErrorMsg();
		}
		
		if (!$result)
		{
			$db->FailtTrans();
		}
		
		$db->CompleteTrans();
		
		return $result;
		
	}
	
	/* Function to pull a file from the database */
	public function Pull($width=null,$height=null)
	{
		
		/* Create the filename */
		$dimensions='';
		
		if($width!==null||$height!==null)
		{
			$dimensions='-'.$width.'-'.$height;
		}
		
		$filename = $this->id . '-' . $this->revision .$dimensions. '.' . substr($this->name,-3);
		
		/* Check if the file exists and if not get it from the db */
		if(!file_exists($this->path . $filename))
		{
				$handle = fopen($this->path . $filename, 'x');
				
				$db = &DB::Instance();
				
				fwrite($handle, $db->BlobDecode($this->file, $this->size));
				
				fclose($handle);
		}
		
		if($width!==null||$height!==null)
		{
			list($width_orig, $height_orig) = getimagesize($this->path.$filename);
			
			if(($width<$width_orig||$height<$height_orig))
			{
				$ratio_orig = $width_orig/$height_orig;

				if ($width==null||$width/$height > $ratio_orig)
				{
				   $width = $height*$ratio_orig;
				}
				else
				{
				   $height = $width/$ratio_orig;
				}
				
				$image_p = imagecreatetruecolor($width, $height);
				
				$image = self::imagecreate($this,$this->path.$filename);
					
				imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);	
				self::imageout($this,$image_p,$this->path.$filename);
			}
		}
		
		return array(
			'filename' => $filename,
			'path' => $this->path
		);
	}
	
	public function save($debug=false)
	{
		$db=&DB::Instance();

		$result=parent::save($debug);
		
		if($result!==false)
		{
			$result=$db->UpdateBlobFile($this->_tablename,'file',$this->tmp_name,'id='.$this->id);
		}
		
		return $result;
	}

	public function SendToBrowser($_display_type = 'attachment')
	{
	    $db = &DB::Instance();
	    
	    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	    header('Content-Transfer-Encoding: binary');
	    header("Content-Disposition: '.$_display_type.'; filename=\"" . $this->name."\";");
	    header('Content-Length: ' . $this->size);
	    header('Content-Type: ' . $this->type);
	    
	    ob_start();
	    
	    echo $db->BlobDecode($this->file, $this->size); 
	    
	    @ob_flush();
	    
	    $content = ob_get_contents();
	    
	    ob_end_clean();
	    
	    echo $content;
	}

	/*
	 * Static Functions
	 */
	public static function Factory(Array $data, Array &$errors,$do_name=null)
	{
		if(empty($data['name']))
		{
			return false;
		}
		
		if(!is_uploaded_file($data['tmp_name']))
		{
			$errors[]='Error with file upload- it would appear you\'re trying to be naughty';
		}
		
		$new_name=FILE_ROOT.'data/tmp/'.uniqid('file');
		
		if(!move_uploaded_file($data['tmp_name'],$new_name))
		{
			$errors[]='Error moving uploaded file, contact the server admin';
		}
		
		if(!chmod($new_name,0655))
		{
			$errors[]='Error changing permission of uploaded file, contact the server admin';
		}
		
		$file=parent::Factory($data,$errors,$do_name);
		
		if($file instanceof File)
		{
			$file->tmp_name=$new_name;
		}
		
		return $file;
	}

	public static function imagecreate($file,$filename)
	{
		switch($file->type)
		{
			case 'image/jpeg':
			case 'image/jpg':
			case 'image/pjpeg':
				$image = imagecreatefromjpeg($filename);
				break;
			case 'image/gif':
				$image = imagecreatefromgif($filename);
				break;
			case 'image/png':
				$image = imagecreatefrompng($filename);
				break;
			default:
				throw new Exception('Unrecognised image format: '.$file->type);
		}
		
		return $image;
	}
	
	public static function imageout($file,$image,$filename)
	{
		switch($file->type)
		{
		case 'image/jpeg':
			case 'image/jpg':
			case 'image/pjpeg':
				imagejpeg($image,$filename,100);
				break;
			case 'image/gif':
				imagegif($image,$filename);
				break;
			case 'image/png':
				imagepng($image,$filename);
				break;
			default:
				throw new Exception('Unrecognised image format: '.$file->type);
		}
		
	}
	
}

// End of File

