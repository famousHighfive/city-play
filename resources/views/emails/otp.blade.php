<x-mail::message>
# Ton code de vérification

Bonjour,

Voici ton code de vérification pour rejoindre la partie **City Play** :

<x-mail::panel>
# {{ $code }}
</x-mail::panel>

Ce code est valable **10 minutes**. Ne le partage avec personne.

Si tu n'as pas demandé ce code, ignore cet email.

Merci,<br>
{{ config('app.name') }}
</x-mail::message>