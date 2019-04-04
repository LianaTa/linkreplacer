<?php
define('DBNAME','linkreplacer');
define('HOST',  'localhost');
define('USER' , 'root');
define('PASSWORD',  'root1');
define('TABLE' ,'links');




function connectDB(){
        
        $mysqli = new mysqli(HOST,USER,PASSWORD,DBNAME);
        if ($mysqli->connect_errno) {
                echo "Не удалось подключиться к MySQL: " . $mysqli->connect_error;
        }
        $mysqli->query('SET NAMES utf8');
        return $mysqli;
}
interface linkreplacer
{
    public function dec_to_link($id);
    public function link_to_dec($link);
    public function add_link_to_db($link_url);
    public function link_replace($link_url);
}
class links implements linkreplacer
{
    private $link_id;
    private $link_hash;
    public  $link_url;


    public function set()
    {

    }

    public function get()
    {

    }

    public function dec_to_link($id)
    {
        $digits='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $link='';
        do {
            $dig=$id%62;
            $link=$digits[$dig].$link;
            $id=floor($id/62);
        } while($id!=0);

        return $link;
    }
    public function link_to_dec($link)
    {
        $digits=Array('0'=>0,  '1'=>1,  '2'=>2,  '3'=>3,  '4'=>4,  '5'=>5,  '6'=>6,  '7'=>7,  '8'=>8,  '9'=>9,
                  'a'=>10, 'b'=>11, 'c'=>12, 'd'=>13, 'e'=>14, 'f'=>15, 'g'=>16, 'h'=>17, 'i'=>18, 'j'=>19,
                  'k'=>20, 'l'=>21, 'm'=>22, 'n'=>23, 'o'=>24, 'p'=>25, 'q'=>26, 'r'=>27, 's'=>28, 't'=>29,
                  'u'=>30, 'v'=>31, 'w'=>32, 'x'=>33, 'y'=>34, 'z'=>35, 'A'=>36, 'B'=>37, 'C'=>38, 'D'=>39,
                  'E'=>40, 'F'=>41, 'G'=>42, 'H'=>43, 'I'=>44, 'J'=>45, 'K'=>46, 'L'=>47, 'M'=>48, 'N'=>49,
                  'O'=>50, 'P'=>51, 'Q'=>52, 'R'=>53, 'S'=>54, 'T'=>55, 'U'=>56, 'V'=>57, 'W'=>58, 'X'=>59,
                  'Y'=>60, 'Z'=>61);
        $id=0;
        for ($i=0; $i<strlen($link); $i++) {
            $id+=$digits[$link[(strlen($link)-$i-1)]]*pow(62,$i);
        }
        return $id;
    }

    public function add_link_to_db($link_url)
    {
        global $mysqli;
        
        $link_url=trim($link_url);
        if ($link_url) {
            if (!preg_match('#^[a-z]{3,}\:#',$link_url)) {
                $link_url='http://'.$link_url;
            }

            
            $query="INSERT INTO `".TABLE."` SET
            `link_hash`='".$link_hash."',
            `link_url`='".$mysqli->real_escape_string($link_url)."'";
            $mysqli->query($query);

            $query="SELECT LAST_INSERT_ID() AS `link_id`";
            $sql_result=$mysqli->query($query);
            $row=$sql_result->fetch_assoc();
            $link_short= $this->dec_to_link($row['link_id']);
        }
        return $link_short;
    }
    public function link_replace($link_url)
    {

        global $mysqli;
        // Проверить, есть ли такая ссылка в базе
        $link_hash=md5($link_url);
        $query="SELECT * FROM `".TABLE."` WHERE `link_hash`='".$link_hash."' LIMIT 1";
        $sql_result = $mysqli->query($query);
        $row=$sql_result->fetch_assoc();
        // Такая ссылка уже есть
        if (isset($row['link_id']))
        {
                $link_short=$this->dec_to_link($row['link_id']);
        }
        // Добавить ссылку в базу
        else 
        {
            $this->add_link_to_db($link_url);
        }

         
        return $link_short;
    }

}
function get_scheme()
{
    if (isset($_SERVER['HTTP_SCHEME']))
    {
        $scheme=strtolower($_SERVER['HTTP_SCHEME']);
    }
    else 
    {
        if ((isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS'])!='off') || $_SERVER['SERVER_PORT']==443) 
        {
            $scheme='https';
        }
        else
        {
            $scheme='http';
        }
    }

    return $scheme;
}
function form_submit($link_url)
{
    
    $link_url =trim($link_url);
    if ($link_url) 
    {
        if (!preg_match('#^[a-z]{3,}\:#',$link_url)) {
                $link_url='http://'.$link_url;
                $scheme = get_scheme();
                $mylink = new links;
                $short_link = $mylink->link_replace($link_url);
                print 'Добавлена ссылка: ';
                print '<input type="text" style="width:350px; font-size:20px;" value="'.$scheme.'://'.getenv('HTTP_HOST').'/'.$link_short.'" onclick="this.select();"><br><br>';

        }
        else
        {
            print 'Введите ссылку корректно!';
        }
    }


}

$mysqli = connectDB();
$link_url = $_POST['url_name'];
form_submit($link_url);
$mysqli->close();
?>
