<?php
class Picture_Controller {
    var $options;

    function __construct($options, $application) {
        $this->options = $options;
        $this->app = $application;
    }

    function get($req) {
        $sql = 'SELECT * FROM picture LIMIT :limit OFFSET :offset';
        $sth = $this->app->db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->bindValue(':limit', (int)$req->pagination['limit'], PDO::PARAM_INT);
        $sth->bindValue(':offset', (int)$req->pagination['offset'], PDO::PARAM_INT);
        $sth->execute();

        $counter = $this->app->db->prepare("SELECT COUNT(*) FROM picture", array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $counter->execute();

        $pages = ceil($counter->fetch()[0] / $req->pagination['limit']);

        $links = new Rest_Link($req->baseUrl);
        $links->add(
            'self',
            '/pics',
            array(
                'page' => $req->pagination['page'],
                'limit' => $req->pagination['limit'],
            )
        );

        $links->add(
            'first',
            '/pics',
            array(
                'page' => 0,
                'limit' => $req->pagination['limit'],
            )
        );

        $links->add(
            'last',
            '/pics',
            array(
                'page' => $pages,
                'limit' => $req->pagination['limit'],
            )
        );

        if ($req->pagination['page']>1) {
            $links->add(
                'previous',
                '/pics',
                array(
                    'page' => $req->pagination['page'] - 1,
                    'limit' => $req->pagination['limit'],
                )
            );
        }

        if ($req->pagination['page']<$pages) {
            $links->add(
                'next',
                '/pics',
                array(
                    'page' => $req->pagination['page'] + 1,
                    'limit' => $req->pagination['limit'],
                )
            );
        }
        $self = $this;
        return array(
            'code' => $pages > (int)$req->pagination['page'] ? 206 : 200,
            'data' => array_map(function($elt) use ($req, $self) {
                $baseUrl = $req->thumb['baseUrl'];
                $elt['filename'] = $baseUrl . $elt['filename'];
                foreach($req->thumb['formats'] as $key) {
                    $elt[$key] = $baseUrl . $elt[$key];
                }
                return $elt;
                }, $sth->fetchAll(PDO::FETCH_ASSOC)),
            'headers' => array(
                'link' => $links,
                'X-Total-Count' => $pages
            )
        );
    }
}
