<?php


namespace App\Service;


use Abraham\TwitterOAuth\TwitterOAuth;
use Psr\Log\LoggerInterface;

class TwitterClient
{

    /**
     * @var TwitterOAuth
     */
    private $twitterOAuth;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(array $consumer, array $access, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->twitterOAuth = new TwitterOAuth($consumer['key'], $consumer['secret'], $access['token'], $access['secret']);
    }

    public function post($path, array $parameters)
    {
        $result = $this->twitterOAuth->post($path, $parameters);
        if ($this->twitterOAuth->getLastHttpCode() == 200) {
            $this->logger->info('Tweet successfully posted');
        } else {
            $reason = $this->getErrorMsg($result);
            $this->logger->error(sprintf('Error posting tweet, "%s"', $reason));
        }
        return $result;
    }

    private function getErrorMsg(object $data): string
    {
        return $data->errors[0]->message;
    }

    public function isConnected(): bool
    {
        $connection = $this->twitterOAuth->get("account/verify_credentials");
        if (!property_exists($connection,'id')) {
            $reason = $this->getErrorMsg($connection);
            $this->logger->warning(sprintf('Twitter connection problem, "%s"', $reason));
            return false;
        }
        return true;
    }

    public function getLastHttpCode(): int
    {
        return $this->twitterOAuth->getLastHttpCode();
    }
}