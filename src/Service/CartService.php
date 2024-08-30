<?php


namespace App\Service;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Entity\Produit;

class CartService
{
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function addCommande(Produit $produit, int $quantite)
    {
        $cart = $this->session->get('cart', []);

        $id = $produit->getId();

        if (!isset($cart[$id])) {
            $cart[$id] = ['produit' => $produit, 'quantite' => 0];
        }

        $cart[$id]['quantite'] += $quantite;

        $this->session->set('cart', $cart);
    }

    public function removeCommande(int $id)
    {
        $cart = $this->session->get('cart', []);
        
        if (isset($cart[$id])) {
            unset($cart[$id]);
        }

        $this->session->set('cart', $cart);
    }

    public function getCommandes()
    {
        return $this->session->get('cart', []);
    }

    public function clear()
    {
        $this->session->remove('cart');
    }
}
