<?php
header('Content-Type: application/json');

class DBRest {
    var $json, $db, $query;  // Objetos en nuestro carrito de compras

    function DBRest($db){
        $this->db = $db;
        $this->query = $_GET['query'];
        $this->query = (preg_match("#^/#", $this->query)) ? substr($this->query, 1, strlen($this->query)) : $this->query;
        $this->query = (preg_match("#/$#", $this->query)) ? substr($this->query, 0, strlen($this->query)-1) : $this->query;
		$this->json = file_get_contents($this->db);
	}

    // ver nodes
    // ?query=users/tantaroth/about
    function get_node() {
        $this->json = json_decode($this->json, true);
        $result = $this->json;
        
        if($this->query != '/'){
            $replace = str_replace('/', '"]["', $this->query);
    		eval('$result = $this->json["'.$replace.'"];');
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
$exp_url = split('/',$_SERVER[REQUEST_URI]);
echo $exp_url[1];
echo "------";
echo $_SERVER[REQUEST_URI];

print_r($_REQUEST);

$_GET['f'] = (isset($_GET['f']))?$_GET['f']:'get_node';
$_GET['p'] = (isset($_GET['p']))?$_GET['p']:'';

$_DBRest = new DBRest('db.json');

echo $_DBRest->$_GET['f']($_GET['p']);
/*echo "<pre>";
print_r($_DBRest->$_GET['f']($_GET['p']));*/
?>