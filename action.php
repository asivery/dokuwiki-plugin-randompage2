<?php
/**
 *
 * @author     Szymon Olewniczak
 */

if(!defined('DOKU_INC')) die();


class action_plugin_randompage2 extends DokuWiki_Action_Plugin {


    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'do_randompage');
    }

    public function do_randompage(Doku_Event $event, $param) {
        if($event->data !== 'randompage') return;
        $event->preventDefault();

        global $conf;
        $dir = $conf['indexdir'];

        $pages = file($dir.'/page.idx');
        shuffle($pages);

        $pattern = $this->getConf('pattern');

        $visited = array();
        while(sizeof($visited) !== sizeof($pages)) {
            $page = trim($pages[rand(0, sizeof($pages) - 1)]);
            if(in_array($page, $visited)) continue;
            array_push($visited, $page);
            if(!page_exists($page)) continue;
            if(isHiddenPage($page)) continue;
            if(strlen($pattern) != 0) {
                if(preg_match($pattern, $page) != 1) continue;
            }
            if (auth_quickaclcheck($page)) {
                send_redirect(wl($page, '', true, '&'));
                return;
            }
        }
    }
}
