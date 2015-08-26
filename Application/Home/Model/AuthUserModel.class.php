<?php
/**
 * Created by IntelliJ IDEA.
 * User: linjiabin
 * Date: 15/8/23
 * Time: 下午3:51
 */

namespace Home\Model;


use Think\Model;

class AuthUserModel extends Model
{
    var $id;
    var $useranme;
    var $password;
}