function updateCoords(c) {
	jQuery('#x').val(c.x);
	jQuery('#y').val(c.y);
	jQuery('#w').val(c.w);
	jQuery('#h').val(c.h);
};

function checkCoords()
{
  if (parseInt(jQuery('#x').val())) return true;
  alert('Please select a crop region then press submit.');
  return false;
};
