<?php namespace Duythien\Controllers;

use Phalcon\Paginator\Adapter\Model as Paginator;
use Duythien\Models\Categories,
	Duythien\Models\Posts,
    Duythien\Models\PostsViews;

/**
 * Display in frontend
 */
class IndexController extends ControllerBase
{

    public function indexAction()
    {
    	$numberPage =1;
    	if ($this->request->getQuery("page", "int")){
    		$numberPage = $this->request->getQuery("page", "int");
    	}
    	$posts 		= Posts::find(array("order" =>"created DESC"));

    	$paginator = new Paginator(array(
			"data" => $posts,
			"limit"=> 10,
			"page" => $numberPage
		));

		$this->view->page = $paginator->getPaginate();
    	$this->view->categories = Categories::find();
        
        
    }
    /**
     * [showAction view signle page]
     * 
     */
    public function showAction()
    {
        $id  = $this->dispatcher->getParam('id');
        
        $post= Posts::findFirstByid($id);
        if (!$post) {
            return $this->response->redirect("index");
        }
        $ipAddress = $this->request->getClientAddress();
        $viewed = PostsViews::count(array(
                'postsId = ?0 AND ipaddress = ?1',
                'bind' => array($id, $ipAddress)
            ));
            /** A view is stored by ipaddress **/
            if (!$viewed) {

                /**
                 * Increase the number of views in the post
                 */
                $post->numberViews++;

                $postView = new PostsViews();
                /**
                 * update numberViews in table post ORM
                 */
                $postView->post = $post;
                $postView->ipaddress = $ipAddress;
                if (!$postView->save()) {
                    foreach ($postView->getMessages() as $message) {
                        $this->flash->error($message);
                    }
                }
            }

        $this->view->post = $post;

    }
    /**
     * Change the language, reload translations if needed
     */
    public function setLanguageAction($language='')
    {
        if ($language == 'en_US' || $language == 'de_DE') {
            $this->session->set('language', $language);
        }

        //Go to the last place
        $referer = $this->request->getHTTPReferer();
        if (strpos($referer, $this->request->getHttpHost()."/")!==false) {
            return $this->response->setHeader("Location", $referer);
        } else {
            return $this->response->redirect();
        }
    }
    /**
     * list post via category
     * 
     */
    public function categoryAction($categoryId, $slug)
    {
        echo "categoryAction";
        $this->view->disable();
    }

    public function initialize()
    {
    	$this->view->setTemplateBefore('public');
    }
    
}

