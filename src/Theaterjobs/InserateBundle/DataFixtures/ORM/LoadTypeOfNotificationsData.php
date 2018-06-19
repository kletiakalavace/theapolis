<?php

namespace Theaterjobs\InserateBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Theaterjobs\UserBundle\Entity\TypeOfNotification;
use Doctrine\Common\DataFixtures\AbstractFixture;

/**
 * Datafixtures for the Gratification.
 *
 * @category DataFixtures
 *
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 *
 * @link     http://www.theaterjobs.de
 */
class LoadTypeOfNotificationsData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    /**
     * The container.
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * Set the container interface.
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Load the fixtures.
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $types = array(
            'An item from my favorite lists is archived' => array('trans' => array('de' => 'Ein Element von meiner Favoriten Listen wird archiviert'), 'requireAction' => false),
            'A new job in my saved search' => array('trans' => array('de' => 'Ein neuer Job in meinem gespeicherte Such'), 'requireAction' => false),
            'A comment to my news' => array('trans' => array('de' => 'Ein Kommentar auf meine Nachrichten'), 'requireAction' => false),
            'An answer to my news comment' => array('trans' => array('de' => 'Eine Antwort auf meine Nachrichten Kommentar'), 'requireAction' => false),
            'An answer to my forum post' => array('trans' => array('de' => 'Eine Antwort auf mein Forum posten'), 'requireAction' => false),
            'An answer to my forum answer' => array('trans' => array('de' => 'Eine Antwort auf mein Forum Antwort'), 'requireAction' => false),
            'Once a week statistics for all published items' => array('trans' => array('de' => 'Einmal in der Woche Statistiken für alle veröffentlichten Artikel'), 'requireAction' => false),
            'When I was revoked from a job team' => array('trans' => array('de' => 'Als ich von einem Job-Team widerrufen'), 'requireAction' => false),
            'When I was added to a job team' => array('trans' => array('de' => 'Wann wurde ich zu einem Job-Team hinzugefügt'), 'requireAction' => false),
            'I have unread messages in my postbox' => array('trans' => array('de' => 'Ich habe ungelesenen Nachrichten in meinem Briefkasten'), 'requireAction' => false),
            'Profile is published' => array('trans' => array('de' => 'Profil veröffentlicht'), 'requireAction' => false, 'code' => 'profile_published'),
            'Profile is unpublished' => array('trans' => array('de' => 'Profile is unpublished'),  'requireAction' => false, 'code' => 'profile_unpublished'),
            'My profile will be archived' => array('trans' => array('de' => 'Mein Profil wird archiviert'), 'requireAction' => true, 'code' => 'profile_not_updated'),
            'My profile was archived' => array('trans' => array('de' => 'Mein Profil archiviert wurde'), 'requireAction' => true),
            'My membership is running out' => array('trans' => array('de' => 'Meine Mitgliedschaft läuft ab'), 'requireAction' => true),
            'My e-mail adress is not working' => array('trans' => array('de' => 'Meine E-Mail Adresse nicht funktioniert'), 'requireAction' => true),
            'I haven\'t inserted a profile' => array('trans' => array('de' => 'Ich habe nicht einen Fragebogen eingeführt'), 'requireAction' => true),
            'My profile is not completed' => array('trans' => array('de' => 'Mein Profil nicht abgeschlossen ist'), 'requireAction' => true),
            'I have a job in drafts' => array('trans' => array('de' => 'Ich habe einen Job in Entwürfe'), 'requireAction' => true),
            'I have an education in drafts' => array('trans' => array('de' => 'Ich habe eine Ausbildung in Entwürfe'), 'requireAction' => true),
            'I have a piece in drafts' => array('trans' => array('de' => 'Ich habe ein Stück in Entwürfe'), 'requireAction' => true),
            'I have a network in drafts' => array('trans' => array('de' => 'Ich habe ein Netzwerk in Entwürfe'), 'requireAction' => true),
            'My job runs out of date' => array('trans' => array('de' => 'Mein Job wird überholt'), 'requireAction' => true, 'code' => 'job_runs_out_of_date'),
            'My piece runs out of date' => array('trans' => array('de' => 'Mein Stück läuft überholt'), 'requireAction' => true),
            'My network runs out of date' => array('trans' => array('de' => 'Mein Netzwerk läuft nicht mehr aktuell'), 'requireAction' => true),
            'My education runs out of date' => array('trans' => array('de' => 'Meine Ausbildung veraltet läuft'), 'requireAction' => true),
            'My profile was rejected by the admin' => array('trans' => array('de' => 'Mein Profil wurde durch den Admin abgelehnt'), 'requireAction' => true),
            'My job is published' => array('trans' => array('de' => 'Mein Job wurde publicied'), 'requireAction' => false, 'code' => 'job_published'),
            'My job is archived' => array('trans' => array('de' => 'Mein Job wurde archiviert'), 'requireAction' => false, 'code' => 'job_archived'),
            'My job is deleted' => array('trans' => array('de' => 'My job is deleted'), 'requireAction' => false, 'code' => 'job_deleted'),
            'New organization join request' => array('trans' => array('de' => 'Nieuwe organisatie verzoek mee'), 'requireAction' => true, 'code' => 'new_orga_request'),
            'New friend request' => array('trans' => array('de' => 'Nieuw vriendenverzoek'), 'requireAction' => true, 'code' => 'new_profile_friend_request'),
            'New job application' => array('trans' => array('de' => 'Nieuwe sollicitatie'), 'requireAction' => true, 'code' => 'new_job_application'),
            'Job application seen' => array('trans' => array('de' => 'Sollicitatie gezien'), 'requireAction' => false, 'code' => 'job_application_seen'),
            'Job application unanswered' => array('trans' => array('de' => 'Sollicitatie onbeantwoord'), 'requireAction' => true, 'code' => 'unanswered_job_application'),
            'Job owner application reply' => array('trans' => array('de' => 'Job eigenaar applicatie antwoord'), 'requireAction' => true, 'code' => 'new_job_application_reply_owner'),
            'Job applicant application reply' => array('trans' => array('de' => 'Sollicitant applicatie antwoord'), 'requireAction' => true, 'code' => 'new_job_application_reply_applicant'),
            'Job application rejected' => array('trans' => array('de' => 'Sollicitatie afgewezen'), 'requireAction' => true, 'code' => 'job_application_rejected'),
            'Job application closed' => array('trans' => array('de' => 'Sollicitatie gesloten'), 'requireAction' => true, 'code' => 'job_applications_closed'),
            'Profile Published' => array('trans' => array('de' => 'profiel Gepubliceerd'), 'requireAction' => false, 'code' => 'profile_published'),
            'Profile Block Withdrawn' => array('trans' => array('de' => 'Blokkeren Ingetrokken'), 'requireAction' => false, 'code' => 'profile_block_withdrawn'),
            'Profile Publish Blocked' => array('trans' => array('de' => 'Profiel publiceren Geblokkeerde'), 'requireAction' => true, 'code' => 'profile_publish_blocked'),
            'Profile Communication Replied' => array('trans' => array('de' => 'Profiel Dialoog Antwoordde'), 'requireAction' => true, 'code' => 'profile_communication_replied'),
            'Membership about to exipire' => array('trans' => array('de' => 'Het lidmaatschap gaat exipire'), 'requireAction' => true, 'code' => 'membership_about_expire'),
            'Membership ended' => array('trans' => array('de' => 'Membership ended'), 'requireAction' => false, 'code' => 'membership_ended'),
            'Membership revoked' => array('trans' => array('de' => 'Membership revoked'), 'requireAction' => false, 'code' => 'membership_revoked'),
            'Become a Member' => array('trans' => array('de' => 'Lid worden'), 'requireAction' => true, 'code' => 'become_member'),
            'Order Received' => array('trans' => array('de' => 'Bestellung erhalten'), 'requireAction' => true, 'code' => 'order_received'),
            'Complain Create' => array('trans' => array('de' => 'Beschweren Erstellen'), 'requireAction' => true, 'code' => 'complain_create'),
            'Complain Reply' => array('trans' => array('de' => 'Beschweren Antworten'), 'requireAction' => true, 'code' => 'complain_reply'),
            'Email Notifications Revoked' => array('trans' => array('de' => 'E-Mail Benachrichtigungen Widerruf'), 'requireAction' => true, 'code' => 'email_notifications_revoked'),
            'Email is not valid anymore' => array('trans' => array('de' => 'E-Mail is not valid'), 'requireAction' => true, 'code' => 'renew_email'),
            'Name change request is Approved' => array('trans' => array('de' => 'Name change request is approved'), 'requireAction' => false, 'code' => 'name_change_approved'),
            'Name change request is Rejected' => array('trans' => array('de' => 'Name change request is rejectMembership direct debit confirm bank dataed'), 'requireAction' => false, 'code' => 'name_change_rejected'),
            'Profile update is old' => array('trans' => array('de' => 'Profile update is old'), 'requireAction' => true, 'code' => 'profile_old_update'),
            'Profile actuality is old' => array('trans' => array('de' => 'Profile actuality is old'), 'requireAction' => true, 'code' => 'profile_old_actuality'),
            'Membership direct debit confirm bank data' => array('trans' => array('de' => 'Membership direct debit confirm bank data'), 'requireAction' => false, 'code' => 'membership_dd_check_bank_data'),
            'Team membership request was Approved' => array('trans' => array('de' => 'Team membership request was Approved'), 'requireAction' => false, 'code' => 'team_membership_request_approved'),
            'Team membership request was Rejected' => array('trans' => array('de' => 'Team membership request was Rejected'), 'requireAction' => false, 'code' => 'team_membership_request_rejected'),
            'User is revoked from organization' => array('trans' => array('de' => 'User is revoked from organization'), 'requireAction' => false, 'code' => 'organization_revoke_member'),
            'User is added to organization' => array('trans' => array('en' => 'User is added to organization'), 'requireAction' => false, 'code' => 'organization_add_member'),
            'Team membership application' => array('trans' => array('en' => 'Team membership application'), 'requireAction' => false, 'code' => 'team_membership_application'),
            'Confirm email for job publication' => array('trans' => array('en' => 'Confirm email for job publication'), 'requireAction' => true, 'code' => 'job_email_confirmation'),
        );
        $repository = $manager->getRepository('Gedmo\\Translatable\\Entity\\Translation');

        foreach ($types as $title => $data) {
            $typeOfNotification = new TypeOfNotification();
            $typeOfNotification->setTitle($title);
            $typeOfNotification->setRequireAction($data['requireAction']);
            if ( isset($data['code']))
                $typeOfNotification->setCode($data['code']);
            foreach ($data['trans'] as $locale => $val) {
                $repository->translate($typeOfNotification, 'title', $locale, $val);
            }

            $manager->persist($typeOfNotification);
            $manager->flush();
            $this->setReference("typeofnotification_$title", $typeOfNotification);
        }
    }

    /**
     * Get the order.
     *
     * @return int $order
     */
    public function getOrder()
    {
        return 40;
    }
}
