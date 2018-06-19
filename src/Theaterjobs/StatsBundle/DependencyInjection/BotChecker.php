<?php

namespace Theaterjobs\StatsBundle\DependencyInjection;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Helper class to find out if the agent is a bot.
 */
class BotChecker
{
    protected $agentList = [ "Yahoo!", "Slurp"];
    protected $request;

    public function __construct(RequestStack $requestStack) {
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * Checks 'User-Agent' based on common bot strings to determine
     * if the agent is a bot.
     *
     * @return boolean Returns true if bot, false otherwise
     */
    public function isBot() {
        $agent = $this->request->headers->get('user-agent');

        if (in_array($agent, $this->agentList))
            return true;
        if (strpos(strtolower($agent), 'bot') !== false)
            return true;
        return false;
    }

    /**
     * Checks 'User-Agent' based on common bot strings to determine
     * if the agent is a human.
     *
     * @return boolean Returns true if human, false otherwise
     */
    public function isHuman() {
        return !$this->isBot();
    }

}
