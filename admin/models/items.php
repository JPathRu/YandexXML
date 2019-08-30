<?php

// No direct access
defined( '_JEXEC' ) or die;
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.application.component.modellist');

/**
 * Yandex.Market XML
 *
 * @version 	1.0
 * @author		Arkadiy, Joomline
 * @copyright	� 2015. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */
class argensymlModelItems extends JModelList
{

    private $folder = 'components/com_argensyml/yml';
	/**
	 * Build a list of authors
	 * @return    JDatabaseQuery
	 */
	public function getItems()
	{
        $files = array();
		$items = parent::getItems();

		if(is_array($items) && count($items))
		{
			foreach($items as $i => $item)
			{
                $ext = $item->type == 'v' ? '.json' : '.xml';
                $filePath = JPATH_ROOT.'/'.$this->folder.'/'.$item->filename.$ext;
                $fileName = $item->filename.$ext;
                $url = JUri::root().$this->folder.'/'.$fileName;

                if(!file_exists($filePath)){
                    $url = '';
                }

                $items[$i]->file = array(
					'name' => $fileName,
					'path' => $filePath,
					'url'  => $url
				);
			}
		}
		else
		{
            $items = array();
		}

		return $items;
	}

    protected function getListQuery()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select('*')
            ->from($db->quoteName('#__argensyml_items'))
            ->order('id ASC')
        ;

        return $query;
    }

    public function getFolder()
    {
        return JPATH_ROOT.'/'.$this->folder;
    }

    public function allow($key)
    {
        $allowedHost = (empty($key)) ? 'localhost' : $key;
        $allowedHost = explode('::', $allowedHost);
        $allow = true;
        foreach($allowedHost as $allowed) {
            $allowed = $this->dsCrypt($allowed, true);
            if(!empty($allowed)){
                $allowed = explode('|', $allowed);
                $site = (!empty($allowed[0])) ? $allowed[0] : 'localhost';
                $extension = (!empty($allowed[1])) ? $allowed[1] : '';
                $expireDate = (!empty($allowed[2])) ? $allowed[2] : '';
                if(strpos($_SERVER['HTTP_HOST'], $site) !== false && $extension == 'com_argensyml'){
                    $allow = true;
                    break;
                }
            }
        }
        return $allow;
    }

    private function dsCrypt($input,$decrypt=false) {
        $o = $s1 = $s2 = array(); // Arrays for: Output, Square1, Square2
        // ��������� ������� ������ � ������� ��������
        $basea = array('?','(','@',';','$','#',"]","&",'*');  // base symbol set
        $basea = array_merge($basea, range('a','z'), range('A','Z'), range(0,9) );
        $basea = array_merge($basea, array('!',')','_','+','|','%','/','[','.',' ') );
        $dimension=9; // of squares
        for($i=0;$i<$dimension;$i++) { // create Squares
            for($j=0;$j<$dimension;$j++) {
                $s1[$i][$j] = $basea[$i*$dimension+$j];
                $s2[$i][$j] = str_rot13($basea[($dimension*$dimension-1) - ($i*$dimension+$j)]);
            }
        }
        unset($basea);
        $m = floor(strlen($input)/2)*2; // !strlen%2
        $symbl = $m==strlen($input) ? '':$input[strlen($input)-1]; // last symbol (unpaired)
        $al = array();
        // crypt/uncrypt pairs of symbols
        for ($ii=0; $ii<$m; $ii+=2) {
            $symb1 = $symbn1 = strval($input[$ii]);
            $symb2 = $symbn2 = strval($input[$ii+1]);
            $a1 = $a2 = array();
            for($i=0;$i<$dimension;$i++) { // search symbols in Squares
                for($j=0;$j<$dimension;$j++) {
                    if ($decrypt) {
                        if ($symb1===strval($s2[$i][$j]) ) $a1=array($i,$j);
                        if ($symb2===strval($s1[$i][$j]) ) $a2=array($i,$j);
                        if (!empty($symbl) && $symbl===strval($s2[$i][$j])) $al=array($i,$j);
                    }
                    else {
                        if ($symb1===strval($s1[$i][$j]) ) $a1=array($i,$j);
                        if ($symb2===strval($s2[$i][$j]) ) $a2=array($i,$j);
                        if (!empty($symbl) && $symbl===strval($s1[$i][$j])) $al=array($i,$j);
                    }
                }
            }
            if (sizeof($a1) && sizeof($a2)) {
                $symbn1 = $decrypt ? $s1[$a1[0]][$a2[1]] : $s2[$a1[0]][$a2[1]];
                $symbn2 = $decrypt ? $s2[$a2[0]][$a1[1]] : $s1[$a2[0]][$a1[1]];
            }
            $o[] = $symbn1.$symbn2;
        }
        if (!empty($symbl) && sizeof($al)) // last symbol
            $o[] = $decrypt ? $s1[$al[1]][$al[0]] : $s2[$al[1]][$al[0]];
        return implode('',$o);
    }
}