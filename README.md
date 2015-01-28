# phpManual
a php manual interface


这是一个php函数查询接口函数，
使用简单，不会用到数据库，
只用了数据文件，
但不会将文件内容全部加载到内存中去查找某个函数的说明，内存占用少，
返回的接送串格式，
使用简单

include_once("./manual/phpManual.php");

$t = new phpManual();
$t->init('zh');

echo $t->get("unpack");
