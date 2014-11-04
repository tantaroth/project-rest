<?php
header('Content-Type: application/json');

class DBRest {
    var $json, $db, $params;  // Objetos en nuestro carrito de compras

    function DBRest($db){
        $this->db = $db;
        $this->json = file_get_contents($this->db);
    }

    // ver nodes
    // /get/users/tantaroth/about
    function get($PARAMS = '*') {
        $this->json = json_decode($this->json, true);
        $this->params = (is_array($PARAMS)) ? (($PARAMS[0] == '') ? '*' : $PARAMS) : $PARAMS;
        $result = $this->json;
        
        if(is_array($this->params)){
            $replace = str_replace('","', '"]["', json_encode($this->params));
            eval('$result = $this->json'.$replace.';');
        }

        return json_encode($result, JSON_PRETTY_PRINT);
    }

    // Agregar nodes
    //?f=add_node&p={"saludo":{"saludo":"hola"}}&query=users/tantaroth/about
    function add_node($new_node) {
        if(!empty($new_node)){
            $this->json = json_decode($this->json, true);
            $json = $this->json;
            
            $replace_query = str_replace('/', '"]["', $this->query);
            
            if(is_array(json_decode($new_node, true))){
                $new_node = str_replace('{', 'array(', $new_node);
                $new_node = str_replace('}', ')', $new_node);
                $new_node = str_replace(':', '=>', $new_node);
                
                eval('$this->json["'.$replace_query.'"] = '.$new_node.';');
            }else eval('$this->json["'.$replace_query.'"] = "'.$new_node.'";');
            
            eval('$this->json["'.$replace_query.'"] = "'.$new_node.'";');
            
            if (file_exists($this->db)) {
                chmod($this->db, 0666);
                
                if (function_exists('file_put_contents')) {
                    file_put_contents($this->db, json_encode($this->json, JSON_PRETTY_PRINT));
                }
            }
    
            return json_encode($this->json, JSON_PRETTY_PRINT);
        }
    }
}
$init = 2;
$exp_url = split('/',$_SERVER[REQUEST_URI]);
$QUERY_URL = $exp_url[$init++];
$PARAMS = array();
echo $exp_url[$init];
echo "------";
echo $_SERVER[REQUEST_URI];

if(
    $QUERY_URL == 'get' ||
    $QUERY_URL == 'add' ||
    $QUERY_URL == 'edit' ||
    $QUERY_URL == 'remove'
){
    $QUERY = $QUERY_URL;
    for($iQUERY=$init+2;$iQUERY<count($exp_url);$iQUERY++){
        $PARAMS[] = $exp_url[$iQUERY];
    }
}else{
    $QUERY = 'get';
    for($iQUERY=$init++;$iQUERY<count($exp_url);$iQUERY++){
        $PARAMS[] = $exp_url[$iQUERY];
    }
}

$_DBRest = new DBRest('db.json');

echo '>> '.$_DBRest->$QUERY($PARAMS);
/*echo "<pre>";
print_r($_DBRest->$_GET['f']($_GET['p']));*/
?>