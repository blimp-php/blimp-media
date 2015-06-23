<?php

namespace Blimp\Media\Rest;

use Pimple\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Blimp\Http\BlimpHttpException;
use League\Glide\ServerFactory;

class Media
{
    public function process(Container $api, Request $request, $id, $field, $index = -1, $_securityDomain = null, $_resourceClass = null, $parent_id = null, $_parentIdField = null, $_parentResourceClass = null)
    {
        if ($_resourceClass == null) {
            throw new BlimpHttpException(Response::HTTP_INTERNAL_SERVER_ERROR, 'Resource class not specified');
        }

        $token = null;
        if ($api->offsetExists('security')) {
            $token = $api['security']->getToken();
        }
        $user = $token !== null ? $token->getUser() : null;

        switch ($request->getMethod()) {
            case 'GET':
                $index = intval($index);

                $contentLang = $api['http.utils']->guessContentLang($request->query->get('locale'), $request->getLanguages());

                $result = $api['dataaccess.mongoodm.utils']->get($_resourceClass, $id, ['fields' => $field], $contentLang, $_securityDomain, $user, $_parentResourceClass, $_parentIdField, $parent_id);

                if (!empty($result) && !empty($result[$field]) && ($index >= 0 || !empty($result[$field][$index]))) {
                    $fileinfo = $index < 0 ? $result[$field] : $result[$field][$index];

                    if (filter_var($fileinfo['file'], FILTER_VALIDATE_URL)) {
                        return new RedirectResponse($fileinfo['file']);
                    } else {
                        try {
                            $path = $fileinfo['bucket'].'/'.$fileinfo['file'];
                            $type = $api['media.filesystems']['user']->getMimetype($path);

                            if (strpos($type, 'image/') === 0) {
                                // Setup Glide server
                                $server = ServerFactory::create([
                                    'source' => $api['media.filesystems']['user'],
                                    'cache' => $api['media.filesystems']['thumb'],
                                ]);

                                $response = $server->getImageResponse($path, $request->query->all());
                                return $response;
                            } elseif ($api['media.filesystems']['user']->has($path)) {
                                $cache_info = array(
                                    'last_modified' => date_create()->setTimestamp($api['media.filesystems']['user']->getTimestamp($path)),
                                    'max_age'       => 31536000,
                                    'private'       => false,
                                    'public'        => true,
                                );
                                
                                $response = new Response();
                                $response->setCache($cache_info);
                                if($response->isNotModified($request)) {
                                    return $response;
                                }
                                
                                $response = new StreamedResponse();
                                $response->setCache($cache_info);
                                $response->setStatusCode(Response::HTTP_OK);
                                $response->headers->set('Content-Type', $type);
                                $response->headers->set('Content-Length', $api['media.filesystems']['user']->getSize($path));
                                $response->setExpires(date_create()->modify('+1 years'));

                                $content = $api['media.filesystems']['user']->readStream($path);

                                $response->setCallback(function () use ($content) {
                                  $out = fopen('php://output', 'wb');

                                  stream_copy_to_stream($content, $out);

                                  fclose($out);
                                  fclose($content);
                                });

                                return $response;
                            }
                        } catch(\League\Flysystem\FileNotFoundException $e) {
                            throw new BlimpHttpException(Response::HTTP_NOT_FOUND, 'Not found');
                        }
                    }
                }

                throw new BlimpHttpException(Response::HTTP_NOT_FOUND, 'Not found');

                break;

            default:
                throw new BlimpHttpException(Response::HTTP_METHOD_NOT_ALLOWED, 'Method not allowed');
        }
    }
}
