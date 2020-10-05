<?php

namespace Compiler\src;

require '../vendor/autoload.php';

class Uploader
{
    /**
     * @var string $targetDir
     * @var bool $uploadOk
     * @var string $targetFile
     * @var string $fileType
     */
    private string $targetDir = "../uploads/";
    private string $targetFile;
    private string $fileType;

    /**
     * Uploader constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->targetFile = $this->targetDir . basename($name);
        $this->fileType = strtolower(pathinfo($this->targetFile, PATHINFO_EXTENSION));
    }

    /**
     * @param string $origin
     * @return bool
     */
    public function moveFile(string $origin): bool
    {
        return move_uploaded_file($origin, $this->targetFile);
    }
}
