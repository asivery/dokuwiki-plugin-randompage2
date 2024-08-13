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

        $namespaces = $this->getConf('namespaces');

        while(true) {
            $page = trim($pages[rand(0, sizeof($pages) - 1)]);
            if(!page_exists($page)) continue;
            if(isHiddenPage($page)) continue;
            if(sizeof($namespaces) != 0) {
                if(!in_array(getNS($page), $namespaces)) continue;
            }
            if (auth_quickaclcheck($page)) {
                send_redirect(wl($page, '', true, '&'));
                return;
            }
        }
    }
}
