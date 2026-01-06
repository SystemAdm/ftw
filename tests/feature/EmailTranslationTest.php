<?php

use App\Models\User;
use App\Notifications\Auth\GuardianInvitation;
use App\Notifications\Auth\GuardianRelationConfirmation;
use App\Notifications\Auth\VerifyEmailWithPin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;

uses(RefreshDatabase::class);

test('guardian invitation email can be translated', function () {
    $minor = User::factory()->create(['name' => 'John Doe']);
    $guardian = User::factory()->create();
    $notification = new GuardianInvitation($minor, 'test@example.com');

    App::setLocale('nb');
    $mail = $notification->toMail($guardian);

    expect($mail->subject)->toBe('Invitasjon til foresatt');
    expect($mail->introLines[0])->toBe('John Doe har registrert seg og oppgitt deg som sin foresatte.');
});

test('guardian confirmation email can be translated', function () {
    $minor = User::factory()->create(['name' => 'Jane Doe']);
    $guardian = User::factory()->create();
    $notification = new GuardianRelationConfirmation($minor);

    App::setLocale('nb');
    $mail = $notification->toMail($guardian);

    expect($mail->subject)->toBe('Bekreftelse av relasjon som foresatt');
    expect($mail->introLines[0])->toBe('Jane Doe har registrert seg og oppgitt deg som sin foresatte.');
});

test('verify email with pin can be translated', function () {
    $user = User::factory()->create();
    $notification = new VerifyEmailWithPin('123456');

    App::setLocale('nb');
    $mail = $notification->toMail($user);

    expect($mail->subject)->toBe('Bekreft e-postadresse');
    expect($mail->introLines[0])->toBe('Din bekreftelseskode (PIN) er:');
});

test('password reset notification can be translated via json', function () {
    $user = User::factory()->create();
    $notification = new \Illuminate\Auth\Notifications\ResetPassword('token');

    App::setLocale('nb');
    $mail = $notification->toMail($user);

    expect($mail->subject)->toBe('Varsel om nullstilling av passord');
    expect($mail->introLines[0])->toBe('Du mottar denne e-posten fordi vi har mottatt en forespørsel om nullstilling av passord for din konto.');
});
