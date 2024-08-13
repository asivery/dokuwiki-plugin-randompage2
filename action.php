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

        $visited = array();
        while(sizeof($visited) !== sizeof($pages)) {
            $page = trim($pages[rand(0, sizeof($pages) - 1)]);
            if(in_array($page, $visited)) continue;
            array_push($visited, $page);
            if(!page_exists($page)) continue;
            if(isHiddenPage($page)) continue;
            if(sizeof($namespaces) != 0) {
                $ok = false;
                $this_ns = getNS($page);
                foreach ($namespaces as $namespace) {
                    if(str_ends_with($namespace, "*")){
                        $namespace = substr($namespace, 0, -1);
                        if(str_starts_with($this_ns, $namespace)) {
                            $ok = true;
                            break;
                        }
                    }elseif(str_starts_with($namespace, "*")){
                        $namespace = substr($namespace, 1);
                        if(str_ends_with($this_ns, $namespace)) {
                            $ok = true;
                            break;
                        }
                    }else {
                        if($namespace === $this_ns) {
                            $ok = true;
                            break;
                        }
                    }
                }
                if(!$ok) continue;
            }
            if (auth_quickaclcheck($page)) {
                send_redirect(wl($page, '', true, '&'));
                return;
            }
        }
    }
}
