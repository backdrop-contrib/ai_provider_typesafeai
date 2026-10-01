# AI Provider TypeSafe AI

TypeSafe AI provider for the Backdrop CMS AI module.

Adds TypeSafe AI's Jev decision models to the providers the `ai` module can
route to, using the `/v1/decisions` endpoint at `https://api.typesafe.ai`. Jev
models return typed judgments with calibrated probabilities instead of
generated text, which suits fast classification and moderation checks.

## Supported operations

| Operation | Supported | Notes |
|---|---|---|
| Decisions | Yes | `boolean`, `choice` and `score` questions via the adapter's `decide()` method. |
| Moderation | Yes | Implemented as a single boolean decision; returns the flag and its probability. |
| Chat | No | |
| Completions | No | |
| Tool calling | No | |
| Vision | No | |
| Embeddings | No | |
| Image generation | No | |
| Speech-to-text | No | |
| Text-to-speech | No | |

## Models

- `jev-1` — standard calibrated decision and moderation model (the default).
- `jev-1-mini` — lighter, faster model for high-throughput checks such as
  entity save or form validation.

The decisions endpoint and request timeout are stored in
`ai_provider_typesafeai.settings` (`endpoint`, `timeout`).

## Installation

- Install this module using the official [Backdrop CMS instructions](https://backdropcms.org/user-guide/modules).
- Create an authentication key with the Key module holding your TypeSafe AI API
  key (https://console.typesafe.ai/keys).
- Enable and configure the provider at `admin/config/ai/settings`, and select
  `jev-1` or `jev-1-mini` as the default decision or moderation model as needed.

## Issues

Bugs and feature requests should be reported in the [Issue Queue](https://github.com/backdrop-contrib/ai_provider_typesafeai/issues).

## Current Maintainer

[Justin Keiser](https://github.com/keiserjb)

## Credits

- Created for Backdrop CMS by [Justin Keiser](https://github.com/keiserjb).

- Developed with AI assistance.

## License

This project is GPL v2 software. See the LICENSE.txt file in this directory for complete text.
