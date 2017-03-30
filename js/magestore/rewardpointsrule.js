function applyRules(link){
    if(link){
        popWin(link,'import','top:0,left:0,width=700,height=400,resizable=yes,scrollbars=yes');
    }
    else
        alert('Rules were applied!');
        return;
}