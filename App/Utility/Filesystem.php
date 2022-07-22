<?php

namespace App\Utility;

use EasySwoole\Utility\File;

class Filesystem
{

    protected $files;

    public function __construct($files = null)
    {
        $this->files = $files;
    }

    /**
     * 保存文件到指定文件夹
     * 指定文件夹下面都是基于时间来生成目录
     * @param string $dirname
     * @return bool|string
     * @throws \Exception
     */
    public function storeAs($dirname = 'upload')
    {
        $time = date('Y-m-d');
        $ext = $this->getExtension();
        $tmpfile = $this->getRealPath();
        $name = $this->hash($tmpfile);
        $fileName = $dirname . '/' . $time . '/' . $name . '.' . $ext;
        $filePath = trim($this->getStaticRoot(), '/') . '/' . $fileName;
        $res = File::createFile($filePath, file_get_contents($tmpfile));
        $domain = $this->getDoMain();
        unlink($tmpfile);
        if ($res === false) throw new \Exception('上传失败');
        return $domain . '/' . $fileName;
    }


    /**
     * 删除指定文件
     * @param $filename
     * @return bool
     */
    public function destroy($filename)
    {
        if ($this->exists($filename)) {
            $filePath = $this->getStaticRoot() . $filename;
            return unlink($filePath);
        }
        return false;
    }

    /**
     * 获得指定文件目录或目录文件数组
     * @param string $dirname
     * @return array|bool
     */
    public function scan($dirname = 'image')
    {
        $filePath = $this->getStaticRoot() . $dirname;
        return File::scanDirectory($filePath);
    }

    /**
     * 判断文件是否存在
     * @param $filename
     * @return bool
     */
    public function exists($filename)
    {
        $filePath = $this->getStaticRoot() . $filename;
        return file_exists($filePath);
    }

    /**
     * 返回项目静态文件夹的绝对地址
     * @return string
     */
    public function getStaticRoot()
    {
        return trim(config('PUBLIC_ROOT'), '/');
    }

    /**
     * 返回项目路由
     * @return string
     */
    public function getDoMain()
    {
        return trim(config('SERVER_URL'), '/');
    }

    /**
     * 获取扩展名
     * @return mixed
     */
    public function getExtension()
    {
        return pathinfo($this->files->getClientFilename(), PATHINFO_EXTENSION);
    }

    /**
     * 临时文件名称
     * @return mixed
     */
    public function getRealPath()
    {
        return $this->files->getTempName();
    }

    /**
     * 获取文件大小
     * @return string
     */
    public function getSize()
    {
        $size = $this->files->getSize();
        return $this->bytesToSize($size);
    }

    /**
     * 字节转化成单位
     * @param $size
     * @return string
     */
    public function bytesToSize($size)
    {
        $units = array(' B', ' KB', ' MB', ' GB', ' TB');
        for ($i = 0; $size >= 1024 && $i < 4; $i++) {
            $size /= 1024;
        }
        return round($size, 2) . $units[$i];
    }

    /**
     * 获取文件类型
     * @return mixed
     */
    public function getType()
    {
        return $this->files->getClientMediaType();
    }

    /**
     * 根据临时文件生成hash值
     * @param string $tmpfile
     * @return string
     * @throws \Exception
     */
    public function hash($tmpfile = '')
    {
        if (empty($tmpfile)) {
            throw new \Exception('临时文件不存在，上传失败');
        }
        return hash_file('md5', $tmpfile);
    }
}