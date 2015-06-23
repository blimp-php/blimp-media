<?php

namespace Blimp\Media;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Blimp\Media\Documents\Media;

class MediaServiceProvider implements ServiceProviderInterface
{
    public function register(Container $api)
    {
        $api['media.store'] = $api->protect(function ($uploadedFile, $bucket, $filename = null, $extension = null) use ($api) {
            if (empty($filename)) {
                $filename = sha1(uniqid(mt_rand(), true));
            }

            if (empty($extension)) {
                $extension = $uploadedFile->guessExtension();

                if (empty($extension)) {
                    $extension = 'bin';
                }
            }

            $stream = fopen($uploadedFile->getRealPath(), 'r+');
            $api['media.filesystems']['user']->writeStream($bucket.'/'.$filename.'.'.$extension, $stream, [
                'visibility' => 'public',
                'mimetype' => $uploadedFile->getMimeType(),
            ]);
            fclose($stream);
                
            return [
                'bucket' => $bucket,
                'file' => $filename.'.'.$extension,
                'originalName' => $uploadedFile->getClientOriginalName()
            ];
        });

        $api['media.store.download'] = $api->protect(function ($url, $bucket, $filename = null, $extension = null) use ($api) {
            if (empty($filename)) {
                $filename = sha1(uniqid(mt_rand(), true));
            }

            if (empty($extension)) {
                $extension = 'bin';
            }

            file_put_contents($api['media.physical.path'].'/'.$bucket.'/'.$filename.'.'.$extension, fopen($url, 'r'));

            $dm = $api['dataaccess.mongoodm.documentmanager']();

            $media = new Media();
            $media->setBasePath($api['media.physical.path']);
            $media->setBucket($bucket);
            $media->setFilePath($filename.'.'.$extension);

            $dm->persist($media);

            return $media;
        });

        $api->extend('blimp.extend', function ($status, $api) {
            if ($status) {
                if ($api->offsetExists('dataaccess.mongoodm.mappings')) {
                    $api->extend('dataaccess.mongoodm.mappings', function ($mappings, $api) {
                        $mappings[] = ['dir' => __DIR__.'/Documents', 'prefix' => 'Blimp\\Media\\Documents\\'];

                        return $mappings;
                    });
                }
            }

            return $status;
        });
    }
}
