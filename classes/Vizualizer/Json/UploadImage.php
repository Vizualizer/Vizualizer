<?php

class Vizualizer_Json_UploadImage
{
    /**
     * ファイルアップロード処理のメインクラス
     */
    public function execute()
    {
        if(!empty($_FILES)){
            $result = array();
            $tmpName = $_FILES["file"]['tmp_name'];
            $result["tmp_name"] = $tmpName;
            if(!is_dir(Vizualizer_Configure::get("upload_root"))){
                mkdir(Vizualizer_Configure::get("upload_root"));
            }
            $destName = date("Y");
            if(!is_dir(Vizualizer_Configure::get("upload_root").DIRECTORY_SEPARATOR.$destName)){
                mkdir(Vizualizer_Configure::get("upload_root").DIRECTORY_SEPARATOR.$destName);
            }
            $destName .= date("md");
            if(!is_dir(Vizualizer_Configure::get("upload_root").DIRECTORY_SEPARATOR.$destName)){
                mkdir(Vizualizer_Configure::get("upload_root").DIRECTORY_SEPARATOR.$destName);
            }
            $result["dest_dir"] = $destName;
            $path = pathinfo($_FILES["file"]['name']);
            $fileName = $path["filename"]."_".date("His").".".$path["extension"];
            $result["dest_file"] = $fileName;
            if (move_uploaded_file($tmpName, Vizualizer_Configure::get("upload_root").DIRECTORY_SEPARATOR.$destName.DIRECTORY_SEPARATOR.$fileName)) {
                $result["filename"] = $destName.DIRECTORY_SEPARATOR.$fileName;
            }else{
                $result["error"] = "Upload Failed.";
            }
            return $result;
        }
    }
}
