<?php
/**
 * Yandex.Market XML
 *
 * @version 	1.0
 * @author		Arkadiy, Joomline
 * @copyright	© 2015. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */
defined('_JEXEC') or die;

?>
{
"date":"<?php echo $this->date; ?>",
"preset":"<?php echo $this->preset; ?>",
"group":"<?php echo $this->group; ?>",
"album":"<?php echo $this->album; ?>",
"categories":[
<?php
if(is_array($this->categories) && count($this->categories))
{
    $i=0;
    foreach ($this->categories as $cat)
    {
        if($i > 0) echo ",\n";
        echo '    ' . json_encode(array('id' => $cat->id, 'vk_id' => $cat->vk_id));
        $i++;
    }
    echo "\n";
}
?>
],
"products":[