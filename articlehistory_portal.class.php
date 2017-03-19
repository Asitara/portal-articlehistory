<?php
/*	Project:	EQdkp-Plus
 *	Package:	Timeline Portal Module
 *	Link:		http://eqdkp-plus.eu
 *
 *	Copyright (C) 2006-2016 EQdkp-Plus Developer Team
 *
 *	This program is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU Affero General Public License as published
 *	by the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU Affero General Public License for more details.
 *
 *	You should have received a copy of the GNU Affero General Public License
 *	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

if( !defined('EQDKP_INC') ){
	header('HTTP/1.0 404 Not Found');exit;
}

class articlehistory_portal extends portal_generic {

	protected static $path	= 'articlehistory';
	protected static $data	= array(
		'name'			=> 'articlehistory',
		'version'		=> '0.1.0',
		'author'		=> 'Asitara',
		'contact'		=> EQDKP_PROJECT_URL,
		'description'	=> 'Shows a history of your articles',
		'lang_prefix'	=> 'articlehistory_',
		'multiple'		=> false,
		'icon'			=> 'fa-archive',
	);
	protected static $apiLevel = 20;
	
	
	public function get_settings($state){
		$arrCategories = $this->pdh->get('article_categories', 'id_list', [true]);
		$settings = array(
			'category'	=> array(
				'type'		=> 'dropdown',
				'options'	=> $this->pdh->aget('article_categories', 'name', 0, [$arrCategories]),
			),
			'timerange'	=> array(
				'type'		=> 'spinner',
				'default'	=> 3,
				'min'		=> 1,
			),
		);
		return $settings;
	}


	public function output(){
		$intCategoryID	= $this->config('category');
		$intTimeRange	= (int)$this->config('timerange');
		$intStartYear	= $this->time->date('Y') - $intTimeRange;
		$arrMonthsNames	= $this->user->lang('time_monthnames');
		
		//fetch all articles
		$arrArticles = $this->pdh->get('article_categories', 'published_id_list', [$intCategoryID, $this->user->id]);
		$arrArticles = $this->pdh->sort(array_unique($arrArticles), 'articles', 'date', 'desc');
		
		// create output array
		$arrOutput = [];
		foreach($arrArticles as $intArticleID){
			$intArticleDate		= $this->pdh->get('articles', 'date', [$intArticleID]);
			$intArticleYear		= $this->time->date('Y', $intArticleDate);
			$intArticleMonth	= $this->time->date('m', $intArticleDate);
			
			if($intArticleYear < $intStartYear) continue;
			
			if(isset($arrOutput[$intArticleYear])){
				if(isset($arrOutput[$intArticleYear][$intArticleMonth])){
					$arrOutput[$intArticleYear][$intArticleMonth]++;
				}else{
					$arrOutput[$intArticleYear][$intArticleMonth] = 1;
				}
			}else{
				$arrOutput[$intArticleYear] = [$intArticleMonth => 1];
			}
		}
		
		// generate output
		$strOutput = '<ul>';
		foreach($arrOutput as $intYear => $arrYear){
			$strOutput .= '<li><h3>'.$intYear.'</h3><ul>';
			foreach($arrYear as $intMonth => $intArticles){
				$strURL		= '';
				$strText	= $arrMonthsNames[((int)$intMonth-1)].' '.$intYear;
				$strTitle	= $arrMonthsNames[((int)$intMonth-1)].' '.$intYear.' ('.$intArticles.' '.(($intArticles > 1)? $this->user->lang('articles') : $this->user->lang('article')).')';
				$strOutput	.= '<li><a href="'.$strURL.'" title="'.$strTitle.'">'.$strText.'</a></li>';
			
			}
			$strOutput .= '</ul></li><br />';
		}
		
		return $strOutput.'</ul>';
	}
}
?>