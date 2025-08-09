@component('mail::message')
# Invitation to Board: {{ $invitation->board->name }}

You have been invited to collaborate on the board "{{ $invitation->board->name }}" as an **{{ ucfirst($invitation->role) }}**.

To accept the invitation, click the button below:

@component('mail::button', ['url' => route('invitations.accept', $invitation->token)])
Accept Invitation
@endcomponent

This invitation will expire on {{ $invitation->expires_at->format('M d, Y') }}.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
