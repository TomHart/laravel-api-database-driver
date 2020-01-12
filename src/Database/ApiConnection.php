<?php


namespace TomHart\Database\Database;

use GuzzleHttp\Client;
use Illuminate\Database\Connection;
use Illuminate\Database\Grammar;
use TomHart\Database\Database\Query\Grammars\ApiGrammar;

class ApiConnection extends Connection
{

    /**
     * Get the default query grammar instance.
     *
     * @return Grammar
     */
    protected function getDefaultQueryGrammar()
    {
        $grammar = app(ApiGrammar::class);
        if ($query = $this->getConfig('query')) {
            $grammar->setDefaultQueryString($query);
        }
        return $this->withTablePrefix($grammar);
    }

    /**
     * Return the host we should be connecting to.
     * @return string
     */
    private function getApiHost()
    {
        return $this->getDatabaseName();
    }

    /**
     * Extract what key in the response the data is held in (if applicable).
     * @param string $query
     * @return string|null
     */
    private function extractJsonKey(string &$query): ?string
    {
        preg_match('/@([a-zA-Z]+)/', $query, $matches);
        $match = array_shift($matches);

        if ($match) {
            $query = str_replace($match, '', $query);
            return $matches[0];
        }

        return 'data';
    }

    /**
     * @param string $query
     * @param mixed[] $bindings
     * @param bool $useReadPdo
     * @return mixed[]
     */
    public function select($query, $bindings = [], $useReadPdo = true)
    {
        return $this->run($query, $bindings, function ($query) {
            $key = $this->extractJsonKey($query);

            $url = $this->getApiHost() . $query;

            /** @var Client $client */
            $client = app(Client::class);
            $json = $this->getResponse($client, $url);

            if (!$key || !isset($json[$key])) {
                return $json;
            }

            return $json[$key];
        });
    }


    /**
     * @param Client $client
     * @param string $url
     * @return mixed[]
     */
    private function getResponse(Client $client, string $url): array
    {
        $response = $client->request('GET', $url, [
            'headers' => config('api-database.headers')
        ]);

        $body = $response->getBody()->getContents();
        $json = \GuzzleHttp\json_decode($body, true);

        if ($this->isPaginatedResponse($json) && $json['current_page'] < $json['last_page']) {
            $json2 = $this->getResponse($client, $json['next_page_url']);

            $json['data'] = array_merge($json['data'], $json2['data']);

            return $json;
        }

        return $json;
    }


    /**
     * Is the JSON responses a paginatable one?
     * @param mixed[] $json
     * @return bool
     */
    private function isPaginatedResponse(array $json): bool
    {
        return empty(array_diff([
            'current_page',
            'data',
            'first_page_url',
            'from',
            'last_page',
            'last_page_url',
            'next_page_url',
            'path',
            'per_page',
            'prev_page_url',
            'to',
            'total'
        ], array_keys($json)));
    }
}
