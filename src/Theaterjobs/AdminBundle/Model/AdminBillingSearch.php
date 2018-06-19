<?php
/**
 * Created by PhpStorm.
 * User: IHoxha
 * Date: 15/03/2018
 * Time: 10:48
 */

namespace Theaterjobs\AdminBundle\Model;


class AdminBillingSearch
{
    const ORDER_TYPE_NEW = 'NEW';
    const ORDER_TYPE_CONTINUE = 'CON';
    const ORDER_TYPE_AGAIN = 'AGAIN';

    protected $input;

    protected $user;

    protected $billingNr;

    protected $billingCreationFrom;

    protected $billingCreationTo;

    protected $billingIban;

    protected $billingPayment;

    protected $billingCountry;

    protected $choices;

    protected $order = 'desc';

    protected $orderCol = 'creation';

    /**
     * @return mixed
     */
    public function getBillingNr()
    {
        return $this->billingNr;
    }

    /**
     * @param mixed $billingNr
     * @return AdminBillingSearch
     */
    public function setBillingNr($billingNr)
    {
        $this->billingNr = $billingNr;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBillingCreationFrom()
    {
        return $this->billingCreationFrom;
    }

    /**
     * @param mixed $billingCreationFrom
     * @return AdminBillingSearch
     */
    public function setBillingCreationFrom($billingCreationFrom)
    {
        $this->billingCreationFrom = $billingCreationFrom;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBillingCreationTo()
    {
        return $this->billingCreationTo;
    }

    /**
     * @param mixed $billingCreationTo
     * @return AdminBillingSearch
     */
    public function setBillingCreationTo($billingCreationTo)
    {
        $this->billingCreationTo = $billingCreationTo;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBillingIban()
    {
        return $this->billingIban;
    }

    /**
     * @param mixed $billingIban
     * @return AdminBillingSearch
     */
    public function setBillingIban($billingIban)
    {
        $this->billingIban = $billingIban;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBillingPayment()
    {
        return $this->billingPayment;
    }

    /**
     * @param mixed $billingPayment
     * @return AdminBillingSearch
     */
    public function setBillingPayment($billingPayment)
    {
        $this->billingPayment = $billingPayment;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBillingCountry()
    {
        return $this->billingCountry;
    }

    /**
     * @param mixed $billingCountry
     * @return AdminBillingSearch
     */
    public function setBillingCountry($billingCountry)
    {
        $this->billingCountry = $billingCountry;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @param mixed $input
     * @return AdminBillingSearch
     */
    public function setInput($input)
    {
        $this->input = $input;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     * @return AdminBillingSearch
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getChoices()
    {
        return $this->choices;
    }

    /**
     * @param mixed $choices
     * @return AdminBillingSearch
     */
    public function setChoices($choices)
    {
        $this->choices = $choices;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param mixed $order
     * @return AdminBillingSearch
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrderCol()
    {
        return $this->orderCol;
    }

    /**
     * @param mixed $orderCol
     * @return AdminBillingSearch
     */
    public function setOrderCol($orderCol)
    {
        $this->orderCol = $orderCol;
        return $this;
    }
}