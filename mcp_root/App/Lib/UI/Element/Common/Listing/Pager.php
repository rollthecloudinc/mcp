<?php 
namespace UI\Element\Common\Listing;

/*
* Semantic pagination interface built using lists
*/
class Pager implements \UI\Element {
	
	public function settings() {
		return array(
			'base_path'=>array( // link building base
				'required'=>true
			)
			,'total_pages'=>array( // total number of pages
				'required'=>true
			)
			,'page'=>array( // current page
				'default'=>1
			)
			,'visible_pages'=>array( // number of pages visible at a time
				'default'=>4
			)
			,'label'=>array( // label for items lists such as; products, blogs, etc
				'default'=>'Items'
			)
		);
	}	

	public function html($settings,\UI\Manager $ui) {
		
		extract($settings);
		if($total_pages <= $visible_pages) {
		    $page_start = 1;
		    $page_end = $total_pages;
		} else if($page <= ceil($visible_pages/2)) {
		    $page_start = 1;
		    $page_end = $visible_pages;
		} else if($page > ($total_pages - ceil($visible_pages/2))) {
		    $page_start = $total_pages - (ceil(($visible_pages/2)*2)-1);
		    $page_end = $total_pages;
		} else {
		    $page_start = $page-(floor($visible_pages/2));
		    $page_end = $page+(floor($visible_pages/2));
		}
		$out=''
		
		$out.= sprintf(
		    '<div class="summary"><p class="pages">%u %s</p><p class="total">%u %s</p></div>'
		    ,$total_pages
		    ,$total_pages == 1?'Page':'Pages'
		    ,$found_rows
		    ,$found_rows == 1?$label:$label
		);
		$out.= sprintf('<ul class="pagination">');
		$out.= sprintf(
		    '<li class="first">%s%s%s</li>'
		    ,$page == 1?'':sprintf('<a href="%s/%u/">',$base_path,1)
		    ,'First'
		    ,$page == 1?'':'</a>'
		);    
		$out.= sprintf(
		    '<li class="previous">%s%s%s</li>'
		    ,$page == 1?'':sprintf('<a href="%s/%u/">',$base_path,($page-1))
		    ,'Previous'
		    ,$page == 1?'':'</a>'
		);
		foreach(range($page_start,$page_end,1) as $i) {
		    /*if($i == $intPageStart) printf('<ol class="pages">');*/
		    $out.= sprintf(
		        '<li%s>%s%s%s</li>'
		        ,$page == $i?' class="current"':''
		        ,$page == $i?'':sprintf('<a href="%s/%u/">',$base_path,$i)
		        ,$i
		        ,$page == $i?'':'</a>'
		    );
		    /*if($i == $intPageEnd) printf('</ol>');*/
		}
		$out.= sprintf(
			'<li class="next">%s%s%s</li>'
			,$page == $total_pages?'':sprintf('<a href="%s/%u/">',$base_path,($page+1))
		    ,'Next'
		    ,$page == $total_pages?'':'</a>'
		);
		$out.= sprintf(
		    '<li class="last">%s%s%s</li>'
		    ,$page == $total_pages?'':sprintf('<a href="%s/%u/">',$base_path,$total_pages)
		    ,'Last'
		    ,$page == $total_pages?'':'</a>'
		);
		$out.= sprintf('</ul>');
		
		return $out;
		
	}
	
}
?>