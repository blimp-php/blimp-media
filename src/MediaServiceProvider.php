<?php
namespace Blimp\Media;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Templating\Helper\SlotsHelper;
use Symfony\Component\Templating\Loader\FilesystemLoader;
use Symfony\Component\Templating\PhpEngine;
use Symfony\Component\Templating\TemplateNameParser;

use Blimp\Media\Documents\Media;

class MediaServiceProvider implements ServiceProviderInterface {
    public function register(Container $api) {
        $api['media.http.path'] = '';
        $api['media.physical.path'] = __DIR__;

        $api['media.store'] = $api->protect(function ($uploadedFile, $bucket, $filename = null, $extension = null) {
            if(empty($filename)) {
                $filename = sha1(uniqid(mt_rand(), true));
            }

            if(empty($extension)) {
                $extension = $uploadedFile->guessExtension();

                if(empty($extension)) {
                    $extension = 'bin';
                }
            }

            $file = $uploadedFile->move($api['media.physical.path'], $filename . '.' . $extension);

            $dm = $api['dataaccess.mongoodm.documentmanager']();

            $media = new Media();
            $media->setBasePath($api['media.physical.path']);
            $media->setRelativePath('/' . $bucket . '/' . $filename);

            $dm->persist($media);

            return $media;
        });
    }
}
