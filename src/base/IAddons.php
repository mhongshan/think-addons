<?php
declare(strict_types=1);

namespace mhs\think\base;

interface IAddons
{
    /**
     * 安装
     * @return boolean
     */
    public function install();

    /**
     * 卸载
     * @return boolean
     */
    public function uninstall();

    /**
     * 启用
     * @return boolean
     */
    public function enable();

    /**
     * 禁用
     * @return boolean
     */
    public function disable();

    /**
     * 升级
     * @return boolean
     */
    public function upgrade();
}