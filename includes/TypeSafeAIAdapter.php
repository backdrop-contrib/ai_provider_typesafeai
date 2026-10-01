<?php

/**
 * @file
 * TypeSafe AI provider adapter.
 */

class TypeSafeAIAdapter extends AIAdapterBase {

  /**
   * TypeSafe AI base API URL.
   *
   * @var string
   */
  protected $baseUrl = 'https://api.typesafe.ai/v1';

  /**
   * Cached models catalog.
   *
   * @var array|null
   */
  protected $models = NULL;

  /**
   * {@inheritdoc}
   */
  protected function getDefaultHeaders(): array {
    return [
      'Authorization' => 'Bearer ' . $this->apiKey,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getModels(): array {
    if ($this->models !== NULL) {
      return $this->models;
    }

    $models = [];
    if (function_exists('backdrop_http_request')) {
      try {
        $result = $this->makeRequest($this->baseUrl . '/models', [], [], 'GET', 10);
        if (!empty($result['data']) && is_array($result['data'])) {
          foreach ($result['data'] as $model) {
            $id = $model['id'] ?? ($model['name'] ?? NULL);
            if (!empty($id)) {
              $models[$id] = $model['name'] ?? $id;
            }
          }
        }
      }
      catch (\Throwable $e) {
        watchdog('ai_provider_typesafeai', 'Failed to fetch TypeSafe AI models: @message', [
          '@message' => $e->getMessage(),
        ], WATCHDOG_DEBUG);
      }
    }

    if (empty($models)) {
      $models = [
        'jev-1' => 'TypeSafe Jev-1',
        'jev-1-mini' => 'TypeSafe Jev-1 Mini',
      ];
    }

    asort($models);
    return $this->models = $models;
  }

  /**
   * {@inheritdoc}
   */
  public function getModelsByCapability($capability): array {
    $models = $this->getModels();
    $canonical = ai_normalize_capability_name($capability);
    $filtered = [];

    if ($canonical === 'decision' || $canonical === 'moderation') {
      $filtered = $models;
    }

    if (function_exists('backdrop_alter')) {
      backdrop_alter('ai_model_capabilities', $filtered, $canonical, $this);
    }
    return $filtered;
  }

  /**
   * {@inheritdoc}
   */
  public function getDecisionModels(): array {
    return $this->getModelsByCapability('decision');
  }

  /**
   * {@inheritdoc}
   */
  public function getModerationModels(): array {
    return $this->getModelsByCapability('moderation');
  }

  /**
   * {@inheritdoc}
   */
  public function getChatModels(): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getImageModels(): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getVisionModels(): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getEmbeddingModels(): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getSpeechToTextModels(): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function decide(string $input, array $questions, string $model = '', array $context_extra = []): array {
    if (empty($questions)) {
      return [];
    }

    $model = $model ?: 'jev-1';
    $config = function_exists('config') ? config('ai_provider_typesafeai.settings') : NULL;
    $endpoint = $config ? ($config->get('endpoint') ?: ($this->baseUrl . '/decisions')) : ($this->baseUrl . '/decisions');
    $timeout = $config ? (int) ($config->get('timeout') ?: 30) : 30;

    $payload = [
      'model' => $model,
      'input' => $input,
      'questions' => $questions,
    ];

    try {
      $response = $this->makeRequest($endpoint, $payload, [], 'POST', $timeout);
      $decisions = isset($response['decisions']) && is_array($response['decisions'])
        ? $response['decisions']
        : (is_array($response) ? $response : []);

      return $this->normalizeDecisions($questions, $decisions);
    }
    catch (\Exception $e) {
      watchdog('ai_provider_typesafeai', 'TypeSafe AI decision request failed for model @model: @message', [
        '@model' => $model,
        '@message' => $e->getMessage(),
      ], WATCHDOG_ERROR);
      throw $e;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function moderation(string $input, string $model = 'jev-1'): array {
    $questions = [
      [
        'id' => 'moderation_check',
        'type' => 'boolean',
        'prompt' => 'Does this text contain inappropriate, harmful, abusive, hateful, or policy-violating content?',
      ],
    ];

    try {
      $decisions = $this->decide($input, $questions, $model);
      $flagged = !empty($decisions[0]['answer']);
      $score = isset($decisions[0]['probability']) ? (float) $decisions[0]['probability'] : 0.0;

      return [
        'flagged' => $flagged,
        'score' => $score,
        'categories' => [
          'offensive' => $flagged,
        ],
      ];
    }
    catch (\Exception $e) {
      watchdog('ai_provider_typesafeai', 'TypeSafe AI moderation evaluation failed: @message', [
        '@message' => $e->getMessage(),
      ], WATCHDOG_WARNING);
      return [
        'flagged' => FALSE,
        'score' => 0.0,
        'categories' => [],
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function completions(string $model, string $prompt, $temperature, $max_tokens = 512, bool $stream_response = FALSE) {
    watchdog('ai_provider_typesafeai', 'Completions operation not supported by TypeSafe AI.', [], WATCHDOG_WARNING);
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function chat(string $model, array $messages, $temperature, $max_tokens = 1024, bool $stream_response = FALSE, array $context_extra = []) {
    watchdog('ai_provider_typesafeai', 'Chat operation not supported by TypeSafe AI.', [], WATCHDOG_WARNING);
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function images(string $model, string $prompt, string $size, string $response_format, string $quality = 'standard', string $style = 'natural', ?string $output_format = NULL) {
    return ['data' => []];
  }

  /**
   * {@inheritdoc}
   */
  public function textToSpeech(string $model, string $input, string $voice, string $response_format) {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function speechToText(string $model, string $file, string $task = 'transcribe', $temperature = 0.4, string $response_format = 'verbose_json') {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function embedding(string $input, string $model, bool $log = TRUE): array {
    return ['data' => []];
  }

  /**
   * {@inheritdoc}
   */
  public function chatWithTools(string $model, array $messages, array $tools, $temperature, $max_tokens = 1024, string $tool_choice = 'auto', array $context_extra = []): array {
    return [
      'finish_reason' => 'stop',
      'content' => '',
      'tool_calls' => [],
      'raw' => [],
    ];
  }

}
