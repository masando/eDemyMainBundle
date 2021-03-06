<?php

namespace eDemy\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

/**
 */
abstract class BaseImagen extends BaseEntity
{
    protected $host;

    public function __construct($em = null, $container = null)
    {
        parent::__construct($em);
    }

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    public function getId()
    {
        return $this->id;
    }

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $path;

    public function getPath()
    {
        return $this->path;
    }

    public function getAbsolutePath($host = null)
    {
        return null === $this->path
            ? null
            : $this->getUploadRootDir($host).'/'.$this->path;
    }

    public function getWebPath($w = null, $h = null, $text = null, $host = null)
    {
        $host = $_SERVER['HTTP_HOST'];
        $parts = explode(".", $host);
        if(count($parts) == 3) {
            $subdomain = $parts[0];
            $domain = $parts[1] . '.' . $parts[2];
        } else {
            $domain = $parts[0] . '.' . $parts[1];
            $subdomain = 'www';
        }

        $text_parts = explode(".", $text);
        if(count($text_parts) == 3) {
            if($text_parts[0] == "beta") {
                $text_parts[0] = "www";
                $text = $text_parts[0] . '.' . $text_parts[1] . '.' . $text_parts[2];
            }

        }
        if(file_exists($this->getAbsolutePath($domain))) {
            $partes_ruta = pathinfo($this->path);
            if(($w != null) and ($h == null)) {
                $path_root = $this->getUploadRootDir($host).'/'.$partes_ruta['filename'].'_w'.$w.'.'.$partes_ruta['extension'];
                $path = $this->getUploadDir($host) . '/' . $partes_ruta['filename'] . '_w' . $w . '.' . $partes_ruta['extension'];
            }
            if(($h != null) and ($w == null)) {
                $path_root = $this->getUploadRootDir($host).'/'.$partes_ruta['filename'].'_h'.$h.'.'.$partes_ruta['extension'];
                $path = $this->getUploadDir($host) . '/' . $partes_ruta['filename'] . '_h' . $h . '.' . $partes_ruta['extension'];
            }
            if($w != null or $h != null) {
                if(!file_exists($path_root)) {
                    try {
                        $image = new \Imagick($this->getAbsolutePath());
                        if(($w != null) and ($h == null)) {
                            $image->thumbnailImage($w, 0);
                        }
                        if(($h != null) and ($w == null)) {
                            $image->thumbnailImage(0, $h);
                        }

                        if($text) {
                            // Create a new drawing palette
                            $draw = new \ImagickDraw();                        

                            // Set font properties
                            $draw->setFont('Helvetica');
                            if(($w + $h) < 100) {
                                $draw->setFontSize(10);
                            } else {
                                $draw->setFontSize(20);
                            }
                            //$draw->setFillOpacity(0);
                            $draw->setFillColor('black');
                            
                            // Position text at the bottom-right of the image
                            //$draw->setGravity(\Imagick::GRAVITY_SOUTHEAST);
                            $draw->setGravity(\Imagick::GRAVITY_CENTER);
                            //$draw->setGravity(\Imagick::GRAVITY_SOUTH);

                            // Draw text on the image
                            $image->annotateImage($draw, 10, 12, 0, $text);
                             
                            // Draw text again slightly offset with a different color
                            $draw->setFillColor('white');
                            $image->annotateImage($draw, 11, 11, 0, $text);
                        }

                        $image->writeImage($path_root);
                    } catch (\Exception $e) {
                        
                    }
                }
            }
            if($this->path === null) {
                return null;
            } else {
                if($w or $h) {
                    $partes_ruta = pathinfo($this->path);

                    return $path;
                } else {
                    return $this->getUploadDir($host).'/'.$this->path;
                }
            }
        } else {
            return null;
        }
    }

    public function setHost($host) {
        $this->host = $host;
    }

    protected function getUploadRootDir($host = null)
    {
        if($this->host) {
            $host = $this->host;
        } else {
            $host = $_SERVER['HTTP_HOST'];
        }
        $parts = explode(".", $host);
        if(count($parts) == 3) {
            $subdomain = $parts[0];
            $domain = $parts[1] . '.' . $parts[2];
        } else {
            $domain = $parts[0] . '.' . $parts[1];
            $subdomain = 'www';
        }
        if($host) {
            $basedir = '/var/www/'.$domain;
        }
        /*else {
            if(strpos(__DIR__, 'app/cache/')) {
                // subimos hasta el directorio raíz de la aplicación (3 niveles)
                $basedir = __DIR__ . '/../../../web';
            } else {
                // si no subimos 6 niveles hasta el directorio raíz de la aplicación
                $basedir = __DIR__ . '/../../../../../../web';
            }
        }
        */
        return $basedir . $this->getUploadDir($host);
    }

    protected function getUploadDir($host = null)
    {

        return '/images';
    }

    public function showPathInPanel()
    {
        return true;
    }

    public function showPathInForm()
    {
        return false;
    }

    

    /**
     * @Assert\File(maxSize="6000000")
     */
    protected $file;

    public function getFile()
    {
        return $this->file;
    }

    public function setFile(File $file = null)
    {
        $this->file = $file;
        // check if we have an old image path
        if (isset($this->path)) {
            // store the old name to delete after the update
            $this->temp = $this->path;
            $this->path = null;
        } else {
            $this->path = 'initial';
        }
    }

    public function showFileInForm()
    {
        return true;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        //die();
        if (null !== $this->getFile()) {
            // do whatever you want to generate a unique name
            $filename = sha1(uniqid(mt_rand(), true));
            $this->path = $filename.'.'.$this->getFile()->guessExtension();
        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        if (null === $this->getFile()) {
            return;
        }

        // if there is an error when moving the file, an exception will
        // be automatically thrown by move(). This will properly prevent
        // the entity from being persisted to the database on error
        $this->getFile()->move($this->getUploadRootDir(), $this->path);

        // check if we have an old image
        if (isset($this->temp)) {
            // delete the old image
            if(file_exists($this->getUploadRootDir().'/'.$this->temp)) {
                unlink($this->getUploadRootDir().'/'.$this->temp);
            }
            // clear the temp image path
            $this->temp = null;
        }
        $this->file = null;
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        if ($file = $this->getAbsolutePath()) {
            unlink($file);
        }
    }
    
    public function showMeta_descriptionInForm() 
    {
        return false;
    }
    public function showMeta_keywordsInForm() 
    {
        return false;
    }
}
