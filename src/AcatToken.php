<?php

namespace ACAT\JWT;

use ACAT\JWT\Exception\TokenException;
use Nowakowskir\JWT\JWT;
use Nowakowskir\JWT\TokenDecoded;
use Nowakowskir\JWT\TokenEncoded;
use function array_key_exists;

/**
 *
 */
class AcatToken {

    /**
     * @var array
     */
    private array $payload;

    /**
     * @var TokenEncoded
     */
    private TokenEncoded $tokenEncoded;

    /**
     * @param string $token
     * @throws TokenException
     */
    public function decode(string $token) : void {

        $this->tokenEncoded = new TokenEncoded($token);
        $this->payload = $this->tokenEncoded->decode()->getPayload();

        if (!array_key_exists('iss',  $this->payload)) {
            throw new TokenException('invalid issuer');
        }

        if (!array_key_exists('name',  $this->payload) || ! $this->payload['name']) {
            throw new TokenException('name is missing');
        }

        if (!array_key_exists('email',  $this->payload) || ! $this->payload['email']) {
            throw new TokenException('email is missing');
        }

        if (!array_key_exists('at_hah',  $this->payload) || ! $this->payload['at_hah']) {
            throw new TokenException('access token is missing');
        }

        if (!array_key_exists('rt_hah',  $this->payload) || ! $this->payload['rt_hah']) {
            throw new TokenException('refresh token is missing');
        }

        if (!array_key_exists('scope',  $this->payload) || ! $this->payload['scope']) {
            throw new TokenException('no scope defined');
        }

        if (!array_key_exists('acat:id',  $this->payload) || ! $this->payload['acat:id']) {
            throw new TokenException('user id is missing');
        }

    }

    /**
     * @return string
     */
    public function getIssuer(): string {
        return $this->payload['iss'];
    }

    /**
     * @param string $issuer
     * @throws TokenException
     */
    public function setIssuer(string $issuer): void {
        if (empty($issuer)) {
            throw new TokenException('issuer verification failed');
        }
        $this->payload['iss'] = $issuer;
    }

    /**
     * @return int
     */
    public function getExpireDate(): int {
        return $this->payload['exp'];
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->payload['name'];
    }

    /**
     * @return string
     */
    public function getEmail(): string {
        return $this->payload['email'];
    }

    /**
     * @return string
     */
    public function getAccessToken(): string {
        return $this->payload['at_hah'];
    }

    /**
     * @return array
     */
    public function getScopes(): array {
        return explode(" ", $this->payload['scope']);
    }

    /**
     * @param string $scope
     * @return void
     */
    public function addScope(string $scope): void {
        if (!str_contains($this->payload['scope'], $scope)) {
            $this->payload['scope'] = trim($this->payload['scope'] . " " . $scope);
        }
    }

    /**
     * @return string
     */
    public function getUserId(): string {
        return $this->payload['acat:id'];
    }

    /**
     * @param string $publicKey
     * @return void
     */
    public function validateToken(string $publicKey) : void {
        $this->tokenEncoded->validate($publicKey, JWT::ALGORITHM_RS256);
    }

    /**
     * @param string $privateKey
     * @return string
     */
    public function encode(string $privateKey) : string {
        return (new TokenDecoded($this->payload))->encode($privateKey, JWT::ALGORITHM_RS256)->toString();
    }
}