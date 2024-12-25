<?php

namespace Kwakuofosuagyeman\AIAssistant\Contracts;

interface AIService
{
    /**
     * Generate text based on a given prompt.
     */
    public function generateText(string $prompt, array $options = []): array;

    /**
     * Generate chat responses for conversational AI.
     */
    public function chat(string $conversation, array $options = []): array;

    /**
     * Analyze the sentiment of a given text.
     */
    public function analyzeSentiment(string $text, array $options = []): array;

    /**
     * Summarize a given text.
     */
    public function summarizeText(string $text, array $options = []): array;

    /**
     * Translate text from one language to another.
     */
    public function translateText(string $text, string $targetLanguage, array $options = []): array;

        /**
     * Generate code or fix code snippets.
     */
    public function generateCode(string $prompt, array $options = []): array;

    /**
     * Generate embeddings for a given text.
     */
    public function generateEmbeddings(string $text, array $options = []): array;

    /**
     * Detect language of the provided text.
     */
    public function detectLanguage(string $text, array $options = []): array;

    /**
     * Generate images from text descriptions (text-to-image).
     */
    public function generateImage(string $description, array $options = []): array;

    /**
     * Modify or enhance an existing image (image editing).
     */
    public function editImage(string $imagePath, array $options = []): array;

    /**
     * Generate speech audio from text (text-to-speech).
     */
    public function generateSpeech(string $text, array $options = []): array;

    /**
     * Transcribe audio to text (speech-to-text).
     */
    public function transcribeAudio(string $audioPath, array $options = []): array;

    /**
     * Moderate or filter content for safety.
     */
    public function moderateContent(string $text, array $options = []): array;

    /**
     * Perform zero-shot classification with the text.
     */
    public function zeroShotClassification(string $text, array $labels, array $options = []): array;



    /**
     * Perform document completion, suggesting text based on input.
     */
    public function completeDocument(string $partialText, array $options = []): array;

    /**
     * Classify text into predefined categories.
     */
    public function classifyText(string $text, array $categories, array $options = []): array;
}
