<?
	$TourneyPreSet = array();

	function cmpPreset($a, $b) {
		return strcasecmp($a['name'], $b['name']);
	}
	
	function LoadTemplates() {
		global $TourneyPreSet, $LS_BASEPATH;
		
		$temppath = $LS_BASEPATH.'../includes/tourney/templates';
		
		$d = dir($temppath);
		while ($s = $d->read()) {
			if ($s != '.' && $s != '..') {
				$template = array();
				include $temppath.'/'.$s;
				usort($template, 'cmpPreset');
				$TourneyPreSet[] = array('desc' => $desc, 'data' => $template);
			}
		}
		
	}
	
	LoadTemplates();
	
?>