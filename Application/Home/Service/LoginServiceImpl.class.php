<?php
/**
 * Created by IntelliJ IDEA.
 * User: linjiabin
 * Date: 15/8/23
 * Time: 下午3:46
 */

namespace Home\Service;


class LoginServiceImpl implements LoginService
{
    function login($username, $passwod)
    {
        if (!$username || !$passwod) {
            return null;
        }
        $user = M("AuthUser");
        $condition = array();
        $condition["username"] = array("eq", $username);
        $condition["password"] = array("eq", md5($passwod));
        $data = $user -> where($condition) -> find();
        return $data;
    }

}