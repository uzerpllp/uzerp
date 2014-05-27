<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class EGSGantt extends EGSChart{

	protected $version='$Revision: 1.2 $';
	
	protected $type='gantt';
	
	/**
	 * @private $num_bars int
	 * The number of GanttBars that have been added to the chart.
	 */
	private $num_bars=0;
	
	/**
	 * @private $project Project
	 * A project model, used to build the Gantt chart
	 */	 
	private $project;
	
	/**
	 * Constructor
	 * @param $project Project
	 * Takes a Project model and initialises some things ready for making a gantt chart
	 * - throws an exception if JPGraph isn't installed. Callers should call EGSGantt::Installed() prior to trying to construct
	 * - requires necessary files
	 * - instantiates $grapher
	 */
	public function __construct(Project $project,$id=null) {
		parent::__construct($project,$project->id);
		require_once APP_ROOT.'plugins/jpgraph/jpgraph_gantt.php';
		$this->grapher = new GanttGraph(800,300);
		$this->project=$project;
	}
	
	
	
	/**
	 * @param $task Task
	 * @return void
	 *
	 * @see makeMilestone()
	 * @see makeGroup()
	 * @see makeNormalbar()
	 *
	 * Takes a task and adds a Bar (or milestone) for it to the chart.
	 * Subsequently gets all subtasks of the task, and adds bars for them
	 */
	public function addGanttBar(Task $task) {
		$i=$this->num_bars;
		$start_date = array_shift(explode(' ',$task->start_date));
		$end_date = array_shift(explode(' ',$task->end_date));
		$children = $task->getChildrenAsDOC();
		if($task->milestone=='t') {
			$bar = $this->makeMilestone($task,$start_date,$i);
		}
		else if(count($children)>0) {
			$bar = $this->makeGroup($task,$start_date,$end_date,$i);
		}
		else {
			$bar = $this->makeNormalbar($task,$start_date,$end_date,$i);
		}
		$bar->title->setFont(FF_ARIAL,FS_NORMAL,10);
		$bar->caption->setFont(FF_ARIAL,FS_NORMAL,10);
		$this->grapher->add($bar);
		$this->num_bars++;
		if(count($children)>0) {
			foreach($children as $child) {
				$this->addGanttBar($child);
			}
		}
	}
	
	
	
	/**
	 * @param $task Task
	 * @param $start_date string
	 * @param $end_date string
	 * @param $position int
	 * @return GanttBar
	 *
	 * Creates a normal GanttBar for a task, and adds an overlay showing the current progress
	 * @todo make colours themeable
	 */
	private function makeNormalbar(Task $task,$start_date,$end_date,$position) {
		$bar = new GanttBar($position,$task->name,$start_date,$end_date,$task->getField('duration')->formatted,15);
		$bar->setPattern(BAND_SOLID,'#0077BD');
		$bar->SetFillColor('#0077BD');
		$bar->progress->Set($task->progress/100);
		$bar->progress->SetPattern(BAND_RDIAG,'#0077BD',70);
		$bar->progress->SetFillColor('#ffffff');
		$bar->setShadow();
		return $bar;
	}
	
	/**
	 * @param $task Task
	 * @param $start_date string
	 * @param $end_date string
	 * @param $position
	 * @return GanttBar
	 *
	 * Create a Gantt bar that represents a task with subtasks, this gives it 'wings' at each end to 
	 * represent it spanning the subtasks.
	 * $start_date and $end_date should be YYYY-MM-DD formatted dates
	 */
	private function makeGroup(Task $task,$start_date,$end_date,$position) {
		$bar = new GanttBar($position,$task->name,$start_date,$end_date,$task->getField('duration')->formatted,8);
		$bar->rightMark->Show();
		$bar->rightMark->SetType(MARK_RIGHTTRIANGLE);
		$bar->rightMark->SetWidth(8);
		$bar->rightMark->SetColor('#0077BD');
		$bar->rightMark->SetFillColor('#0077BD');
		$bar->leftMark->Show();
		$bar->leftMark->SetType(MARK_LEFTTRIANGLE);
		$bar->leftMark->SetWidth(8);
		$bar->leftMark->SetColor('#0077BD');
		$bar->leftMark->SetFillColor('#0077BD');

		$bar->setColor('#0077BD');
		$bar->SetPattern(BAND_SOLID,'#0077BD');
		return $bar;
	}
	
	/**
	 * @param $task Task
	 * @param $start_date string
	 * @param $position int
	 * @return Milestone
	 *
	 * Creates a Milestone object (JPGraph), set to the start_date
	 * $start_date should be YYYY-MM-DD formatted dates
	 */
	private function makeMilestone(Task $task, $start_date,$position) {
		$ms = new MileStone($position,$task->name,$task->start_date,$task->start_date);
		$ms->setLabelLeftMargin(20);
		return $ms;
	}
	
	
	/**
	 * @return void
	 *
	 * Handles the setting up of the chart, ready to render it
	 * @todo make colours themeable
	 * @todo remove reliance on TTF (check somehow?)
	 */
	public function process() {
		if(file_exists($this->makeFileName(true))) {
			unlink($this->makeFileName(true));
		}
		$this->grapher->setShadow();
		$this->grapher->ShowHeaders(GANTT_HMONTH | GANTT_HDAY | GANTT_HWEEK);
		$this->grapher->scale->week->SetStyle(WEEKSTYLE_FIRSTDAY);
		$this->grapher->scale->week->SetFont(FF_ARIAL,FS_NORMAL,8);
		$this->grapher->scale->month->SetFont(FF_ARIAL,FS_BOLD,10);
		$this->grapher->SetBackgroundGradient('white','#0077BD',GRAD_HOR,BGRAD_MARGIN);
	}
	
	
	
}
?>
