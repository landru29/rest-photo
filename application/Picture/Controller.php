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

        return array(
            'data' => array(
                'values' => $sth->fetchAll(PDO::FETCH_ASSOC),
                'pages' => $pages,
            ),
            'code' => 200
        );
    }
}
