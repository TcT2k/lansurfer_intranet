
function ShowMatch(id) {
	var left =  (screen.availHeight - 500) / 2;
	var top =  (screen.availHeight - 520) / 2;
	window.open("match_details.php?id=" + id, "MatchDetail", "height=500,width=520,left="+left+",top="+top+",locationbar=0,menubar=0,resizable=1,scrollbars=1,status=0");
}

function ShowTeam(id) {
	var left =  (screen.availHeight - 500) / 2;
	var top =  (screen.availHeight - 520) / 2;
	window.open("team_detail.php?id=" + id, "TeamDetail", "height=500,width=520,left="+left+",top="+top+",locationbar=0,menubar=0,resizable=1,scrollbars=1,status=0");
}

var oldcolor;
var oldid;

function CellOver(cell)
{
   if (!cell.contains(event.fromElement))
   {
   		oldid = cell.id;
      cell.id = 'TourneyHover';
   }
}

function CellOut(cell)
{
   if (!cell.contains(event.toElement))
   {
      cell.id = oldid;
   }
}

function CellClick(cell)
{
   if(event.srcElement.tagName=='TD')
   {
      cell.children.tags('A')[0].click();
   }
}
