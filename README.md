# AI Provider TypeSafe AI

TypeSafe AI provider for the Backdrop CMS AI module.

Adds TypeSafe AI's decision models to the providers the `ai` module can route
to, using the `/v1/systemone` endpoint at `https://api.typesafe.ai`. These
models return typed judgments with calibrated probabilities instead of
generated text, which suits fast classification and moderation checks.

## Supported operations

| Operation | Supported | Notes |
|---|---|---|
| Decisions | Yes | `boolean`, `choice` and `score` questions via `decide()`; choice questions take `options`, score questions optional `levels` (2-10, low to high). |
| Moderation | Yes | Implemented as a single boolean decision; `score` is the probability of a violation. |
| Chat | No | |
| Completions | No | |
| Tool calling | No | |
| Vision | No | |
| Embeddings | No | |
| Image generation | No | |
| Speech-to-text | No | |
| Text-to-speech | No | |

## Models

The model list is fetched from TypeSafe AI's `/v1/models` endpoint, so new
models appear without a module update. When no model is chosen, the first
listed model is used. See https://docs.typesafe.ai/models.

The decision endpoint and request timeout are stored in
`ai_provider_typesafeai.settings` (`endpoint`, `timeout`).

## Installation

- Install this module using the official [Backdrop CMS instructions](https://backdropcms.org/user-guide/modules).
- Create an authentication key with the Key module holding your TypeSafe AI API
  key (https://console.typesafe.ai/keys).
- Enable and configure the provider at `admin/config/ai/settings`, and select
  a default decision or moderation model as needed.

## Issues

Bugs and feature requests should be reported in the [Issue Queue](https://github.com/backdrop-contrib/ai_provider_typesafeai/issues).

## Current Maintainer

[Justin Keiser](https://github.com/keiserjb)

## Credits

- Created for Backdrop CMS by [Justin Keiser](https://github.com/keiserjb).

- Developed with AI assistance.

## License

This project is GPL v2 software. See the LICENSE.txt file in this directory for complete text.
