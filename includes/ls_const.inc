<?
// LanSurfer Consts
	require $LS_BASEPATH.'../includes/ls_version_info.inc';

// Gast Flags
	define('GUEST_PAID', 			1);			// Der Gast hat bereits bezahlt
	define('GUEST_VIP', 			2);			// VIP
	define('GUEST_EVENING', 	4);			// Abendkasse
	define('GUEST_PREPAID', 	8);			// 
	define('GUEST_ONLOCATION',16);			// 

// News optionen
	define('NEWS_PUBLIC',		1);
	define('NEWS_TEAM',			2);	

// Auth optionen
	define('AUTH_TEAM',					1);
	define('AUTH_LS_INTERN',		2);
	define('AUTH_TOURNEY',			3);
	
// Team Rights	
	define('TEAM_PARTY', 				1);
	define('TEAM_NEWS', 				2);
	define('TEAM_NEWS_ALL', 		4);
	define('TEAM_DESIGN', 			8);
	define('TEAM_FORUM',			 16);
	define('TEAM_USER',			 	 32);
	define('TEAM_GUEST',		 	 64);
	define('TEAM_TOURNEY',		128);
	define('TEAM_CATERING',		256);

// Seat Status
	define('SEAT_DELETED',			1);
	define('SEAT_OCCUPIED',			2);
	define('SEAT_RESERVED',			3);

// Design Style
	define('STYLE_NORMAL',			1);
	define('STYLE_NOINNER',			2);
	define('STYLE_NAVBAR',			3);

// Posting Optionen
	define('POSTING_LATEST', 		1);		// Letztes Posting in zu dem Topic/NewsItem
	define('POSTING_NEWS', 			2);		// Posting gehört zum Newsitem 

// Posting Typen
	define('PT_TOPIC',			0);
	define('PT_NEWS',				1);
	
// Forum Options
	define('FORUM_PRIVATE',			1);		// never show in overview pages
	define('FORUM_TEAM',				2);		// Only Team members may enter this forum
	define('FORUM_NOANONYMOUS', 4);		// Disallow Anonymous (non login) postings

// Forum Topic Options
	define('FTO_CLOSED',			1);

?>