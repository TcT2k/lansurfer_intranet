<?
	/*
	 *  Default tournament template file
	 *  
	 *  (*) Denotes a required field
	 *
	 */
	$desc = '*default';									// Description of this tournament template file
	
	$template = array(
		array(
			'MaxTeams' => 64,								// Maximum number of teams that can register (default = 64)
			'DELimit' => 2048,							// Double Elimination Limit 
																			// > MaxTeams means complete Double Elimination
																			// = 0 means complete double elimination
																			// (default = 2048)
			'name' => 'QuakeWorld 1on1',		// Name of tournament (*)
			'rules' => '1on1 - Quake.htm',	// Filename of rules file
			'icon' => 'quake.gif',					// filename of icon file
			'TeamSize' => 1,								// Maximum size of teams (default = 1 means 1on1)
			
			// Times (all Times in minutes)
			// Time for one round = MatchPause + Games * GameLength
			'MatchPause' => 10,							// Spare time for every tournament round
			'Games' => 2,										// Games that should be played
			'GameLength' => 10,
			
			'ScoreName' => 'Frags',					// Scorename (default = "Points")
			'DrawHandling' => DRAW_SINGLE,	// Draw game handling DRAW_SINGLE, DRAW_SINGLE_HALF_TIME, DRAW_DOUBLE_HALF_TIME (default = DRAW_SINGLE)
			'MapList' => 'dm2\ndm4\ndm6',		// List of maps each map entry seperated by \n
			'TeamType' => '',								// Team types every type seperated by \n like Blue\nRed\n (default = '')
			'ScoreType' => SCORE_DEFAULT,		// Score Type SCORE_RELATIVE, SCORE_ABSOLUTE (default = SCORE_DEFAULT)
			),
		array(
			'name' => 'Quake 2 1on1',
			'rules' => '1on1 - Quake2.htm',
			'icon' => 'quake2.gif',
			'TeamSize' => 1,
			'MatchPause' => 10,
			'Games' => 2,
			'GameLength' => 10,
			'ScoreName' => 'Frags',
			'DrawHandling' => DRAW_SINGLE,
			'MapList' => 'q2dm1\nq2dm2\nq2dm3\nq2dm4\nq2dm5\nq2dm6\nq2dm7\nq2dm8\nmatch1',
			),
		array(
			'name' => 'Doom 1on1',
			'rules' => '1on1 - Doom.htm',
			'icon' => 'doom.gif',
			'TeamSize' => 1,
			'MatchPause' => 10,
			'Games' => 2,
			'GameLength' => 10,
			'ScoreName' => 'Frags',
			'DrawHandling' => DRAW_SINGLE,
			'MapList' => 'map01\nmap07',
			),
		array(
			'name' => "Halflife 1on1",
			'rules' => '1on1 - Halflife.htm',
			'TeamSize' => 1,
			'MatchPause' => 10,
			'Games' => 2,
			'GameLength' => 10,
			'ScoreName' => 'Frags',
			'icon' => 'halflife.gif',
			),
		array(
			'name' => "Quake 3 Arena CTF 6on6",
			'rules' => '',
			'TeamSize' => 6,
			'MatchPause' => 10,
			'Games' => 2,
			'GameLength' => 20,
			'ScoreName' => 'Captures',
			'icon' => 'quake3.gif',
			)
	);
?>