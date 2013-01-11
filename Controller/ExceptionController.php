<?php 

namespace Tactics\Bundle\AdminBundle\Controller;

use Symfony\Component\HttpKernel\Exception\FlattenException;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
 
class ExceptionController extends Controller
 {
 /**
 * Converts an Exception to a Response.
 *
 * @param FlattenException $exception A FlattenException instance
 * @param DebugLoggerInterface $logger A DebugLoggerInterface instance
 * @param string $format The format to use for rendering (html, xml, …)
 * @param Boolean $embedded Whether the rendered Response will be embedded or not
 *
 * @throws \InvalidArgumentException When the exception template does not exist
 */
 public function exceptionAction(FlattenException $exception, DebugLoggerInterface $logger = null, $format = 'html', $embedded = false)
 {
    $statusCode = $exception->getStatusCode();
    $statusText = isset(Response::$statusTexts[$statusCode]) ? Response::$statusTexts[$statusCode] : '';
    
    $arraytopass= array('status_code' => $statusCode, 'status_text' => $statusText);
    
    if($statusCode == '404') {
        return $this->render('TacticsAdminBundle:Exception:error404.html.twig');
    }
    elseif($statusCode == '403') {
        return $this->render('TacticsAdminBundle:Exception:error403.html.twig');        
    }
    else {
        return $this->render('TacticsAdminBundle:Exception:error.html.twig',$arraytopass);        
    }
    
 }
 
}
 ?>
