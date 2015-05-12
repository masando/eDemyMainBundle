<?php

namespace eDemy\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
//use JMS\DiExtraBundle\Annotation\Service;
use eDemy\MainBundle\Entity\Product;
use eDemy\MainBundle\Entity\Cart;

/**
 * @Route("/cart")
 */
class CartController extends Controller
{
    /**
     * @Route("/", name="cart")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $cart = $this->get('edemy.cart');
		$host = $frontcontroller = $this->getRequest()->getHost();
		$env = $this->container->getParameter('kernel.environment');
		if($env == 'dev') {
			$frontcontroller = $host . '/app_dev.php';
        }

        if( $cart->isNotEmpty() ) {
            foreach($cart->getItems() as $item){
                $deleteForms[$item->getProductId()] = $this->createDeleteForm($item->getProductId())->createView();
            }
            $discountForm = $this->createDiscountForm();
            $discountForm->handleRequest($request);
            if($discountForm->isValid()){
                $cart = $this->get('edemy.cart');
                $cart->addDiscount();
            }
        } else {
            $deleteForms = null;
        }

        return array(
			'host' => $host,
			'env' => $env,
			'frontcontroller' => $frontcontroller,
            'cart' => $cart,
            'items' => $cart->getItems(),
            'deleteForms' => $deleteForms,
            'discountform' => $discountForm->createView(),
        );
    }
    /**
     * @Route("/add/{id}", name="cart_add")
     */
    public function addAction(Product $product)
    {
        $cart = $this->get('edemy.cart');
        $cart->addProduct($product);

        return $this->redirect($this->generateUrl('cart'));
    }

    /**
     * @Route("/remove/{id}", name="cart_remove")
     * @Method({"GET","DELETE"})
     */
    public function removeAction(Product $product)
    {
        $cart = $this->get('edemy.cart');
        $cart->removeProduct($product);

        return $this->redirect($this->generateUrl('cart'));
    }
    /**
     * @Template()
     */
    public function trayAction()
    {
        $cart = $this->get('edemy.cart');

        return array(
            'cart' => $cart,
        );
    }
    /**
     * @Template()
     */
    public function showImageAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $product = $em->getRepository('eDemyMainBundle:Product')->find($id);

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }
    
        return array(
            'path' => $product->getWebPath(),
        );
    }
   /**
     * Creates a form to delete a Product entity in the cart by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        $msg = $this->get('translator')->trans('cart.remove');
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('cart_remove', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => $msg))
            ->getForm()
        ;
    }
   /**
     * Creates a discount form
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDiscountForm()
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('cart'))
            ->setMethod('POST')
            ->add('discount', null, array('label' => false, 'required' => false))
            ->add('submit', 'submit', array('label' => 'cart.discount'))
            ->getForm()
        ;
    }	
    /**
     * @Route("/notify", name="cart_notify")
     * @Template()
     */
    public function notifyAction(Request $request)
    {
		$msg = "";
		$host = $frontcontroller = $this->getRequest()->getHost();
		$env = $this->container->getParameter('kernel.environment');
		if($host == 'beta.blenderseyewear.es' or $env == 'dev'){
			$mailmsg = 'pedido de prueba';
			$mailto = 'manuel@edemy.es';
		} else {
			$mailmsg = 'Nuevo pedido';
			$mailto = 'pedidos@blenderseyewear.es';
		}
		
		foreach($request->request as $key => $value) {
			$msg .= "<" . $key . " " . $value . ">\n";
		}

		$message = \Swift_Message::newInstance()
			->setSubject($mailmsg)
			->setFrom('hola@blenderseyewear.es')
			->setTo($mailto)
			->setBody($msg)
		;
		$this->get('mailer')->send($message);

		return $this->redirect($this->generateUrl('cart'));

        return array(
            'cart' => $cart,
            'items' => $cart->getItems(),
            'deleteForms' => $deleteForms,
        );
    }
    /**
     * @Route("/success", name="cart_success")
     * @Template()
     */
    public function successAction(Request $request)
    {
        $this->get('edemy.cart')->clear();

        return array();
    }
	
}
