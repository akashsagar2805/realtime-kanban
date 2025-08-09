@component('mail::message')
# Board Invitation

You have been invited to collaborate on the board **{{ $invitation->board->title }}** as a **{{ $invitation->role }}**.

To accept the invitation, click the button below:

@component('mail::button', ['url' => url('/invitations/' . $invitation->token . '/accept')])
Accept Invitation
@endcomponent

If you did not expect to receive this invitation, you can ignore this email.

Thanks,
{{ config('app.name') }}
@endcomponent
