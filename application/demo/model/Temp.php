<?php
namespace app\demo\model;

use think\Model;
use traits\model\SoftDelete;

class Temp extends Model
{
    use SoftDelete;
    protected $table = 'dp_demo_user';
    protected $autoWriteTimestamp = true;
    
}
