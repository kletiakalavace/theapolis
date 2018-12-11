<?php

namespace Theaterjobs\MembershipBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContext;
use Theaterjobs\MembershipBundle\Entity\DebitAccount;
use Theaterjobs\MembershipBundle\Service\Sepa;


/**
 * Form Type for debitAccount
 *
 *
 * @category Form
 * @package  Theaterjobs\MembershipBundle\Controller
 * @author   Heiko Jurgeleit <heiko@theaterjobs.de>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 * @DI\FormType
 */
class DebitAccountType extends AbstractType {

    /** @DI\Inject("doctrine.orm.entity_manager") */
    public $em;

    /** @DI\Inject("translator") */
    public $translator;

    /**
     * @DI\Inject("theaterjobs_membership.sepa")
     * @var Sepa $sepa
     */
    public $sepa;


    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('accountHolder', 'text', array(
            'required' => true,
            'label' => 'membership.new.label.accountHolder',
                )
        )
        ->add('iban', 'text', array(
            'required' => true,
            'label' => 'membership.new.label.iban',
            'constraints' => [
                new Assert\Callback([$this, 'isBlacklistIban'])
            ]
        ));
    }

    /**
     * @inheritdoc
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(
                array(
                    'translation_domain' => 'forms',
                    'data_class' => 'Theaterjobs\MembershipBundle\Entity\DebitAccount',
                    'mapped' => false
                )
        );
    }

    /**
     * (non-PHPdoc) @see \Symfony\Component\Form\FormTypeInterface::getName()
     */
    public function getName() {
        return 'theaterjobs_membership_debit_account_type';
    }

    /**
     * @param $value
     * @param ExecutionContext $context
     */
    public function isBlacklistIban($value, ExecutionContext $context)
    {
        $form = $context->getRoot();
        /** @var DebitAccount $debitAccount */
        $debitAccount = $form->getData();
        $booking = $debitAccount->getProfile()->getLastBooking();

        if($booking && $booking->getPaymentmethod()->isDebit()){
            $valid = $this->sepa->checkIban($value);
            if (!$valid) {
                $context->buildViolation($this->translator->trans('account.settings.Iban.not.valid'))->addViolation();
                return;
            }

            $blackListIban = $this->em->getRepository('TheaterjobsMembershipBundle:IbanBlacklist')->countBlacklisted($value);
            if ($blackListIban) {
                $context->buildViolation($this->translator->trans('Iban is blacklisted'))->addViolation();
            }
        }
    }

}
