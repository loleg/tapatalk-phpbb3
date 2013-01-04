<?php
/**
* @package module_install
*/
class acp_mobiquo_info
{
    function module()
    {     
        return array(
            'filename'  => 'acp_mobiquo',
            'title'     => 'Tapatalk',    
        	'version'	=> '3.6.1',  
            'modes'     => array(
            	'mobiquo'  => array(
            		'title' => 'ACP_MOBIQUO_SETTINGS', 
            		'auth' => 'acl_a_board', 
            		'cat' => array('ACP_MOBIQUO')
        		),
        		'mobiquo_rebranding' => array(
					'title'	=> 'ACP_TAPATALK_REBRANDING',
					'auth'  => 'acl_a_board',
        			'cat'   => array('ACP_MOBIQUO')
        		)
            ),
        );
    }
 
    function install()
    {
    }
 
    function uninstall()
    {
    }
}
?>