<?
	/*
	 *  WWCL tournament template file
	 *
	 *  http://wwcl.net/
	 *
	 */
	$desc = 'WWCL 5th Season - <a target="_blank" href="http://wwcl.net/">http://wwcl.net/</a>';
	
	$template = array(
		 array(
			'name' => "Counter-Strike 5on5",
			'rules' => 'wwcl_hlcs_2on2-5on5.pdf',
			'TeamSize' => 5,
			'MatchPause' => 10,
			'Games' => 2,
			'GameLength' => 40,
			'ScoreName' => 'Wins',
			'icon' => 'counterstrike.gif',
			'WWCLType' => 7,
			'-wwcl' => true,
			'-WWCLMainContest' => true
			),
		 array(
			'name' => "Serious Sam 2 1on1",
			'rules' => 'wwcl_sesam2_1on1.pdf',
			'TeamSize' => 1,
			'MatchPause' => 15,
			'Games' => 2,
			'GameLength' => 10,
			'ScoreName' => 'Frags',
			'icon' => 'serioussam.gif',
			'WWCLType' => 12,
			'-wwcl' => true,
			'-WWCLMainContest' => true
			),
		 array(
			'name' => "Need for Speed 1on1",
			'rules' => 'wwcl_nfs_1on1.pdf',
			'TeamSize' => 1,
			'MatchPause' => 15,
			'Games' => 2,
			'GameLength' => 15,
			'ScoreName' => 'Points',
			'icon' => 'nfshs.gif',
			'WWCLType' => 11,
			'-wwcl' => true,
			'-WWCLMainContest' => true
			),
		 array(
			'name' => "Warcraft 3 1on1",
			'rules' => 'wwcl_wc3_1on1-2on2.pdf',
			'TeamSize' => 1,
			'MatchPause' => 30,
			'Games' => 2,
			'GameLength' => 30,
			'ScoreName' => 'Points',
			'icon' => '',
			'WWCLType' => 31,
			'-wwcl' => true,
			'-WWCLMainContest' => true
			),
		 array(
			'name' => "Battlefield 1942 8on8",
			'rules' => 'wwcl_bf1942_8on8.pdf',
			'TeamSize' => 8,
			'MatchPause' => 10,
			'Games' => 2,
			'GameLength' => 25,
			'ScoreName' => 'Ticketpoints',
			'icon' => '',
			'WWCLType' => 33,
			'-wwcl' => true,
			'-WWCLMainContest' => true
			),
		 array(
			'name' => "Broodwar 1on1",
			'rules' => 'wwcl_scbw_1on1-2on2.pdf',
			'TeamSize' => 1,
			'MatchPause' => 15,
			'Games' => 2,
			'GameLength' => 30,
			'ScoreName' => 'Points',
			'icon' => 'starcraft.gif',
			'WWCLType' => 10,
			'-wwcl' => true,
			'-WWCLMainContest' => false
			),
		 array(
			'name' => "Broodwar 2on2",
			'rules' => 'wwcl_scbw_1on1-2on2.pdf',
			'TeamSize' => 2,
			'MatchPause' => 15,
			'Games' => 2,
			'GameLength' => 30,
			'ScoreName' => 'Points',
			'icon' => 'starcraft.gif',
			'WWCLType' => 9,
			'-wwcl' => true,
			'-WWCLMainContest' => false
			),
		 array(
			'name' => "Age of Mythology 1on1",
			'rules' => 'wwcl_aom_1on1.pdf',
			'TeamSize' => 1,
			'MatchPause' => 10,
			'Games' => 2,
			'GameLength' => 40,
			'ScoreName' => 'Points',
			'icon' => 'aoe2.gif',
			'WWCLType' => 15,
			'-wwcl' => true,
			'-WWCLMainContest' => false
			),
		 array(
			'name' => "Counter-Strike 2on2",
			'rules' => 'wwcl_cs_2on2-5on5.pdf',
			'TeamSize' => 2,
			'MatchPause' => 10,
			'Games' => 2,
			'GameLength' => 40,
			'ScoreName' => 'Wins',
			'icon' => 'counterstrike.gif',
			'WWCLType' => 24,
			'-wwcl' => true,
			'-WWCLMainContest' => false
			),
		 array(
			'name' => "Warcraft 3 2on2",
			'rules' => 'wwcl_wc3_1on1-2on2.pdf',
			'TeamSize' => 2,
			'MatchPause' => 30,
			'Games' => 2,
			'GameLength' => 30,
			'ScoreName' => 'Points',
			'icon' => '',
			'WWCLType' => 32,
			'-wwcl' => true,
			'-WWCLMainContest' => false
			),
		 array(
			'name' => "Medal of Honor 1on1",
			'rules' => 'wwcl_mohaa_1on1.pdf',
			'TeamSize' => 1,
			'MatchPause' => 20,
			'Games' => 2,
			'GameLength' => 20,
			'ScoreName' => 'Wins',
			'icon' => '',
			'WWCLType' => 35,
			'-wwcl' => true,
			'-WWCLMainContest' => false
			),
		 array(
			'name' => "Medal of Honor OBJ 5on5",
			'rules' => 'wwcl_mohaa_obj_5on5.pdf',
			'TeamSize' => 5,
			'MatchPause' => 10,
			'Games' => 2,
			'GameLength' => 40,
			'ScoreName' => 'Wins',
			'icon' => '',
			'WWCLType' => 42,
			'-wwcl' => true,
			'-WWCLMainContest' => false
			),
		 array(
			'name' => "Blobby Volley 1on1",
			'rules' => 'wwcl_bv_1on1.pdf',
			'TeamSize' => 1,
			'MatchPause' => 5,
			'Games' => 2,
			'GameLength' => 15,
			'ScoreName' => 'Points',
			'icon' => '',
			'WWCLType' => 46,
			'-wwcl' => true,
			'-WWCLMainContest' => false
			),
		 array(
			'name' => "Enemy Territory 6on6",
			'rules' => 'wwcl_wet_6on6.pdf',
			'TeamSize' => 6,
			'MatchPause' => 15,
			'Games' => 2,
			'GameLength' => 30,
			'ScoreName' => 'Points',
			'icon' => '',
			'WWCLType' => 47,
			'-wwcl' => true,
			'-WWCLMainContest' => false
			)
	);
?>