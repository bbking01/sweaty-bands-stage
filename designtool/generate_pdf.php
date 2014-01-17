<?php 
	require '../app/Mage.php';
	Mage::app();
	
  $path = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).'designtool/';	
?>


<?php
$isAddStoreCode = Mage::getStoreConfig('web/url/use_store');
$order_id = $_GET['order_id'];
$storeCode = $_GET['store_code'];
$store_id = $_GET['store_id'];

?>

<div class="flashwidth">
<script type='text/javascript' src='<?php echo $path ?>script/swfobject.js'></script>
<script type='text/javascript'>
var flashvars = {};

<?php
if($order_id != '')
?>
flashvars.order_id= <?php echo $order_id; ?>;
flashvars.isAddStore = '<?php echo $isAddStoreCode; ?>';
flashvars.storeCode = '<?php echo $storeCode; ?>';
flashvars.user= 'admin';
var parameters = {};
parameters.salign = 'tl';
var attributes = {};

swfobject.embedSWF('<?php echo $path ?>designStudio.swf?t=<?php echo date('Y-m-d H:i:s'); ?>', 'designStudioDiv', '100%', '705', '10.0.0', false, flashvars, parameters, attributes);
</script>

<div id='designStudioDiv'>
<p>This page requires Adobe Flash Player, which you can download free at <a href="http://get.adobe.com/flashplayer/">http://get.adobe.com/flashplayer/</a></p>
</div>
</div>
<script language="JavaScript">
window.onbeforeunload = WindowCloseHanlder;
function WindowCloseHanlder()
{
//window.alert('My Window is reloading');
window.opener.location.reload();

}
</script>	