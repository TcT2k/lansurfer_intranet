<?
	/*
	 *  WWCL tournament template file
	 *
	 *  http://wwcl.net/
	 *
	 */
	$desc = 'WWCL - <a target="_blank" href="http://wwcl.net/">http://wwcl.net/</a>';
	
	$template = array(
		 array(
			'name' => "Quake 3 Arena 1on1",
			'rules' => 'wwcl_q3a_1on1.pdf',
			'TeamSize' => 1,
			'MatchPause' => 10,
			'Games' => 2,
			'GameLength' => 10,
			'ScoreName' => 'Frags',
			'icon' => 'quake3.gif',
			'MapList' => "q3tourney2\nhub3tourney1\nztn3tourney1\npro-q3tourney4\npro-q3dm13",
			'WWCLType' => 1,
			'-wwcl' => true
			),
		 array(
			'name' => "Quake 3 Arena 4on4",
			'rules' => 'wwcl_q3a_4on4.pdf',
			'TeamSize' => 4,
			'MatchPause' => 10,
			'Games' => 2,
			'GameLength' => 20,
			'ScoreName' => 'Frags',
			'icon' => 'quake3.gif',
			'MapList' => "pro-q3dm6\nq3dm14tmp\ncpm4",
			'WWCLType' => 2,
			'-wwcl' => true
			),
		 array(
			'name' => "Unreal Tournament CTF 5on5",
			'rules' => 'wwcl_ut_ctf.pdf',
			'TeamSize' => 5,
			'MatchPause' => 10,
			'Games' => 2,
			'GameLength' => 20,
			'ScoreName' => 'Captures',
			'icon' => 'unreal.gif',
			'WWCLType' => 3,
			'-wwcl' => true
			),
		 array(
			'name' => "Unreal Tournament TDMPro 4on4",
			'rules' => 'wwcl_ut_tdmpro.pdf',
			'TeamSize' => 4,
			'MatchPause' => 10,
			'Games' => 2,
			'GameLength' => 20,
			'ScoreName' => 'Frags',
			'icon' => 'unreal.gif',
			'WWCLType' => 4,
			'-wwcl' => true
			),
		 array(
			'name' => "Unreal Tournament 1on1",
			'rules' => 'wwcl_ut_1on1.pdf',
			'TeamSize' => 1,
			'MatchPause' => 10,
			'Games' => 2,
			'GameLength' => 10,
			'ScoreName' => 'Frags',
			'icon' => 'unreal.gif',
			'WWCLType' => 5,
			'-wwcl' => true
			),
		 array(
			'name' => "Halflife 4on4",
			'rules' => 'wwcl_hl_4on4.pdf',
			'TeamSize' => 4,
			'MatchPause' => 10,
			'Games' => 2,
			'GameLength' => 20,
			'ScoreName' => 'Frags',
			'icon' => 'halflife.gif',
			'MapList' => "boot_camp\ndatacore\nlost_village2\noutcry\nrustmill\nstalkyard\ntqm_village",
			'WWCLType' => 6,
			'-wwcl' => true
			),
		 array(
			'name' => "Counter-Strike 5on5",
			'rules' => 'wwcl_cs_5on5.pdf',
			'TeamSize' => 5,
			'MatchPause' => 10,
			'Games' => 2,
			'GameLength' => 20,
			'ScoreName' => 'Wins',
			'icon' => 'counterstrike.gif',
			'WWCLType' => 7,
			'-wwcl' => true
			),
		 array(
			'name' => "Team Fortress Classic 8on8",
			'rules' => 'wwcl_tfc_8on8.pdf',
			'TeamSize' => 8,
			'MatchPause' => 10,
			'Games' => 2,
			'GameLength' => 25,
			'ScoreName' => 'Frags',
			'icon' => 'tfc.gif',
			'WWCLType' => 8,
			'-wwcl' => true
			),
		 array(
			'name' => "Broodwar 2on2",
			'rules' => 'wwcl_bw.pdf',
			'TeamSize' => 2,
			'MatchPause' => 10,
			'Games' => 2,
			'GameLength' => 10,
			'ScoreName' => 'Points',
			'icon' => 'starcraft.gif',
			'WWCLType' => 9,
			'-wwcl' => true
			),
		 array(
			'name' => "Broodwar 1on1",
			'rules' => 'wwcl_bw.pdf',
			'TeamSize' => 1,
			'MatchPause' => 10,
			'Games' => 2,
			'GameLength' => 10,
			'ScoreName' => 'Points',
			'icon' => 'starcraft.gif',
			'WWCLType' => 10,
			'-wwcl' => true
			),
		 array(
			'name' => "Need for Speed 1on1",
			'rules' => 'wwcl_nfs-pu.pdf',
			'TeamSize' => 1,
			'MatchPause' => 10,
			'Games' => 2,
			'GameLength' => 10,
			'ScoreName' => 'Points',
			'icon' => 'nfshs.gif',
			'WWCLType' => 11,
			'-wwcl' => true
			),
		 array(
			'name' => "Serious Sam 1on1",
			'rules' => 'wwcl_serious.pdf',
			'TeamSize' => 1,
			'MatchPause' => 10,
			'Games' => 2,
			'GameLength' => 10,
			'ScoreName' => 'Frags',
			'icon' => 'serioussam.gif',
			'WWCLType' => 12,
			'-wwcl' => true
			),
		 array(
			'name' => "Grand Prix 3 1on1",
			'rules' => 'wwcl_gp3.pdf',
			'TeamSize' => 1,
			'MatchPause' => 10,
			'Games' => 3,
			'GameLength' => 10,
			'ScoreName' => 'Points',
			'icon' => '',
			'DELimit' => 0,
			'WWCLType' => 13,
			'-wwcl' => true
			),
		 array(
			'name' => "AOE2 1on1",
			'rules' => 'wwcl_aoe2.pdf',
			'TeamSize' => 1,
			'MatchPause' => 10,
			'Games' => 2,
			'GameLength' => 10,
			'ScoreName' => 'Points',
			'icon' => 'aoe2.gif',
			'WWCLType' => 14,
			'-wwcl' => true
			)
	);
?>