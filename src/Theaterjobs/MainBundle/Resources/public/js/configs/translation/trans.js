var DEFAULT_LOCALE = "de";

/**
 * Translation function, return translated word specified by key and locale
 *
 * @param key
 *
 * @param locale
 *
 * @param params translation params
 */
function trans(key, locale, params) {
    if (typeof key === "undefined" || typeof locale === "undefined") {
        throw new Error('key and locale are required on trans function');
    }
    if (typeof transArray[key] !== "undefined") {

        if (typeof transArray[key][locale] !== "undefined") {

            return replaceParams(transArray[key][locale], params);

        } else {
            return replaceParams(transArray[key][DEFAULT_LOCALE], params)
        }
    }
    return key;
}

/**
 * Replace params in translation keys
 * @param str
 * @param params
 * @returns {*}
 */
function replaceParams(str, params) {
    if (typeof params !== "undefined") {
        for (let param in params) {
            const regex = new RegExp(param, "g");
            str = str.replace(regex, params[param]);
        }
    }
    return str;
}


var transArray =
    {
        'translation_key': {
            'en': 'Hello world',
            'sq': 'Pershendetje bote',
            'de': 'Hallo Welt'
        },
        'bootbox.savedsearch': {
            'en': 'bootbox.savedsearch',
            'sq': 'To find your saved searches, click on your name in the upper navigation-bar and click Searches or click',
            'de': 'Deine gespeicherten Suchen findest Du in Deinem'
        },
        'link.here': {
            'en': 'link.here',
            'sq': 'here',
            'de': 'Benutzer-Fenster'
        },
        'bootbox.wantToDelete': {
            'en': 'bootbox.wantToDelete',
            'sq': 'Are you sure you want to delete this?',
            'de': 'Möchtest Du das wirklich löschen?'
        },
        'bootbox.changeNotification': {
            'en': 'bootbox.changeNotification',
            'sq': 'Are you sure you want to change notification status?',
            'de': 'Möchtest Du wirklich keine täglichen Benachrichtigungen mehr?'
        },
        'bootbox.button.yes': {
            'en': 'bootbox.button.yes',
            'sq': 'Yes',
            'de': 'Ja'
        },
        'bootbox.button.no': {
            'en': 'bootbox.button.no',
            'sq': 'No',
            'de': 'Nein'
        },
        'bootbox.removeMember': {
            'en': 'bootbox.removeMember',
            'sq': 'Do you really want to revoke the right of the user on this organization?',
            'de': 'Möchtest du wirklich die Rechte löschen?'
        },
        'bootbox.button.ok': {
            'en': 'bootbox.button.ok',
            'sq': 'Ok',
            'de': 'OK'
        },
        'bootbox.button.cancel': {
            'en': 'bootbox.button.cancel',
            'sq': 'Cancel',
            'de': 'Abbrechen'
        },
        'link.readLess': {
            'en': 'link.readLess',
            'sq': 'Read less',
            'de': 'Weniger'
        },
        'link.readFullBio →': {
            'en': 'link.readFullBio →',
            'sq': 'Read full bio →',
            'de': 'Mehr →'
        },
        'registration.label.readAccept': {
            'en': 'registration.label.readAccept',
            'sq': 'I have read and accept the',
            'de': 'Ich akzeptiere die'
        },
        'password.tooWeak': {
            'en': 'password.tooWeak',
            'sq': 'Too weak',
            'de': 'Sehr schwach'
        },
        'password.weak': {
            'en': 'password.weak',
            'sq': 'Weak',
            'de': 'Schwach'
        },
        'password.strong': {
            'en': 'password.strong',
            'sq': 'Strong',
            'de': 'Stark'
        },
        'password.empty': {
            'en': 'password.empty',
            'sq': 'Empty',
            'de': 'Das Passwort-Feld ist leer'
        },
        'password.register.empty': {
            'en': 'password.register.empty',
            'sq': 'Empty',
            'de': 'password.register.empty'
        },
        'max.organization.limit': {
            'en': 'max.organization.limit',
            'sq': 'Max organization limit is only one',
            'de': 'Es kann nur eine Organisation ausgewählt werden'
        },
        'link.seeMore': {
            'en': 'link.seeMore',
            'sq': 'See more',
            'de': 'Mehr'
        },
        'link.seeLess': {
            'en': 'link.seeLess',
            'sq': 'See less',
            'de': 'Weniger'
        },
        'add.new.stage': {
            'en': 'add.new.stage',
            'sq': 'Add new Stage',
            'de': 'Neue Spielstätte hinzufügen'
        },
        'bootbox.desc.toUseThisFeaturePlease': {
            'en': 'bootbox.desc.toUseThisFeaturePlease',
            'sq': 'To use this feature, please',
            'de': 'Bühneneingang'
        },
        'bootbox.link.becomeMember': {
            'en': 'bootbox.link.becomeMember',
            'sq': 'Become a member',
            'de': 'Jetzt Mitglied werden'
        },
        'login.modal.tooltip.badCredencial': {
            'en': 'login.modal.tooltip.badCredencial',
            'sq': 'Bad credencials',
            'de': 'Oops, die Anmeldedaten sind nicht gültig'
        },
        'reset.modal.tooltip.invalidEmail': {
            'en': 'login.modal.tooltip.invalidEmail',
            'sq': 'Invalid Email address',
            'de': 'Oops, die E-Mail-Adresse ist ungültig'
        },
        'profile.unpublished.first': {
            'en': 'profile.unpublished.first',
            'sq': 'Unpublished your profile first',
            'de': 'Oops, bitte deaktiviere vorher Dein Profil',
        },
        'flash.error.profile.unPublished.contact.notAllowedEmpty': {
            'en': 'flash.error.profile.unPublished.contact.notAllowedEmpty',
            'sq': 'Not allowed to delete data before unpublishing your profile',
            'de': 'Oops, bitte deaktiviere erst Dein Profil, bevor Du Deine Kontaktdaten löschst'
        },
        'maxOrganization.limit.onlyOne': {
            'en': 'maxOrganization.limit.onlyOne',
            'sq': 'Max organization limit is only one',
            'de': 'Es kann nur eine Organisation ausgewählt werden'
        },
        'bootbox.discardCopy': {
            'en': 'bootbox.discardCopy',
            'sq': 'Do you want to discard this copy?',
            'de': 'Möchtest Du wirklich den Vorgang abbrechen?'
        },
        'login.modal.error.confirm_token.first': {
            'en': 'login.modal.error.confirm_token.first',
            'sq': 'Confirm registration link',
            'de': 'Bitte klicke vor dem ersten Einloggen auf den Registrierungslink'
        },
        'tooltip.newPasswordRequired': {
            'en': 'tooltip.newPasswordRequired',
            'sq': 'New password required',
            'de': 'Bitte gib ein neues Passwort ein'
        },
        'tooltip.oldPasswordRequired': {
            'en': 'tooltip.oldPasswordRequired',
            'sq': 'Old password required',
            'de': 'Bitte gib Dein altes Passwort ein'
        },
        'tooltip.password.validate.Length': {
            'en': 'tooltip.password.validate.Length',
            'sq': 'New password should be at least 8 character',
            'de': 'Bitte gib ein Passwort mit mindestens 8 Zeichen ein'
        },
        'tooltip.passwordconfirmationRequired': {
            'en': 'tooltip.passwordconfirmationRequired',
            'sq': 'New password confirmation is required',
            'de': 'Bitte wiederhole Dein neues Passwort'
        },
        'tooltip.passwordconfirmationdontMatch': {
            'en': 'tooltip.passwordconfirmationdontMatch',
            'sq': 'Password confirmation dont match',
            'de': 'Die Passwörter stimmen nicht überein'
        },
        'registration.label.dataSecurity': {
            'en': 'registration.label.dataSecurity',
            'sq': 'Data security',
            'de': 'Datenschutzerklärung'
        },
        'registration.label.and': {
            'en': 'registration.label.and',
            'sq': 'and',
            'de': 'und'
        },
        'experience.maxOrganization.limit.onlyOne': {
            'en': 'experience.maxOrganization.limit.onlyOne',
            'sq': 'Max organization limit is only one',
            'de': 'Es kann nur ein Eintrag ausgewählt werden'
        },
        'accountSettings.emailchange.awaiting.approval': {
            'en': 'accountSettings.emailchange.awaiting.approval',
            'sq': 'We have send an verification email for the email change,confirm it. ',
            'de': 'Eine E-Mail ist an Deine neue Adresse unterwegs. Bitte klicke auf den Bestätigungslink.'
        },
        'organization.show.block.desc.link.readLessDesc': {
            'en': 'organization.show.block.desc.link.readLessDesc',
            'sq': 'Read less ',
            'de': 'Weniger'
        },
        'organization.show.block.desc.link.readFullDesc': {
            'en': 'organization.show.block.desc.link.readFullDesc',
            'sq': 'Read more ',
            'de': 'Mehr'
        },
        'pdf.filesize.less.then.x.size': {
            'en': 'pdf.filesize.less.then.x.size',
            'sq': 'Large pdf file, should be les then 20 MB ',
            'de': 'Bitte nimm ein PDF kleiner als 20 MB'
        },
        'tooltip.error.thisFieldIsRequired': {
            'en': 'tooltip.error.thisFieldIsRequired',
            'sq': 'This field is required ',
            'de': 'Bitte fülle dieses Feld aus'
        },
        'profile.addPhoto': {
            'en': 'profile.addPhoto',
            'sq': 'Please add a profile photo ',
            'de': 'Bitte lade ein Profilfoto hoch'
        },
        'bootbox.addFavorite': {
            'en': 'bootbox.addFavorite',
            'sq': 'Added to favorite',
            'de': 'Deine gespeicherten Favoriten findest Du in Deinem'
        },
        'bootbox.addfavorite.Here': {
            'en': 'bootbox.addfavorite.Here',
            'sq': 'click here to see it',
            'de': 'Benutzerfenster'
        },
        'profile.unpublish.bootbox.confirmtext': {
            'en': 'profile.unpublish.bootbox.confirmtext',
            'sq': 'Are you sure do you really want to unpublish the profile',
            'de': 'Möchtest Du Dein Profil wirklich deaktivieren'
        },
        'profile.unpublish.bootbox.button.Yes': {
            'en': 'profile.unpublish.bootbox.button.Yes',
            'sq': 'Yes',
            'de': 'Ja'
        },
        'profile.unpublish.bootbox.button.No': {
            'en': 'profile.unpublish.bootbox.button.No',
            'sq': 'No',
            'de': 'Nein'
        },
        'max.upload.profile.pixc.size.is.10M': {
            'en': 'max.upload.profile.pixc.size.is.10M',
            'sq': 'Max upload size is 10M',
            'de': 'Bitte lade eine Datei kleiner als 10MB hoch'
        },
        'organization.max.limit.only.one': {
            'en': 'organization.max.limit.only.one',
            'sq': 'only 1 organization is possible',
            'de': 'Es kann nur ein Eintrag ausgewählt werden'
        },
        'director.max.limit.only.one': {
            'en': 'director.max.limit.only.one',
            'sq': 'Max director limit is only one',
            'de': 'Es kann nur ein Eintrag ausgewählt werden'
        },
        'creator.max.limit.only.one': {
            'en': 'creator.max.limit.only.one',
            'sq': 'Max creator limit is only one',
            'de': 'Es kann nur ein Eintrag ausgewählt werden'
        },
        'people.edit.creator.newCreator': {
            'en': 'people.edit.creator.newCreator',
            'sq': 'new creator',
            'de': 'neuer Urheber'
        },
        'people.edit.creator.newDirector': {
            'en': 'people.edit.creator.newDirector',
            'sq': 'new director',
            'de': 'neue Leitung'
        },
        'account.setting.membershipbox.quickcontractBootbox': {
            'en': 'account.setting.membershipbox.quickcontractBootbox',
            'sq': 'Are you sure?',
            'de': 'Möchtest Du wirklich Deine Mitgliedschaft kündigen?'
        },
        'map.marker.Here': {
            'en': 'map.marker.Here',
            'sq': 'You are here, drag the marker',
            'de': 'Zum Ändern des Standorts ziehen'
        },
        'bootbox.address.not.found': {
            'en': 'bootbox.address.not.found',
            'sq': 'Address not found',
            'de': 'Die Adresse konnte leider nicht gefunden werden'
        },
        'accountSettings.email.fix.success': {
            'en': 'accountSettings.email.fix.success',
            'sq': 'Email change successfully',
            'de': 'Deine E-Mail-Einstellungen wurden aktualisiert'
        },
        'tooltip.choosePaymentMethod': {
            'en': 'tooltip.choosePaymentMethod',
            'sq': 'Please choose a payment method',
            'de': 'Bitte wähle eine Zahlungsart'
        },
        'membership.new.bankName': {
            'en': 'membership.new.bankName',
            'sq': 'Bank name',
            'de': 'Name der Bank'
        },
        'error.occurred.pleaseReload': {
            'en': 'membership.new.bankName',
            'sq': 'Ein Fehler ist aufgetreten, die Seite neu laden',
            'de': 'Ein Fehler ist aufgetreten, bitte lade die Seite neu'
        },
        'people.production.newcreator': {
            'en': 'people.production.newcreator',
            'sq': 'new Creator',
            'de': 'neuer Urheber'
        },
        'people.production.newdirector': {
            'en': 'people.production.newdirector',
            'sq': 'new Director',
            'de': 'neue Leitung'
        },
        'people.production.newOrganization': {
            'en': 'people.production.newOrganization',
            'sq': 'new Organization',
            'de': 'neue Organisation'
        },
        'people.production.newProduction': {
            'en': 'people.production.newProduction',
            'sq': 'new Production',
            'de': 'neue Produktion'
        },
        'people.production.maxcreator.is': {
            'en': 'people.production.newcreator',
            'sq': 'new Creator',
            'de': 'Es kann nur ein Eintrag ausgewählt werden'
        },
        'people.production.maxdirector.is': {
            'en': 'people.production.newdirector',
            'sq': 'new Director',
            'de': 'Es kann nur ein Eintrag ausgewählt werden'
        },
        'password.reset.successfully': {
            'en': 'password.reset.successfully',
            'sq': 'Password reset successfully',
            'de': 'Das Passwort wurde erfolgreich geändert.'
        },
        'file.max.size.is {0}': {
            'en': 'file.max.size.is {0}',
            'sq': 'Maximum file size allowed is {0} Mb',
            'de': 'Bitte wähle eine Dateigröße, kleiner als {0} Mb'
        },
        'file.type.invalid': {
            'en': 'file.type.invalid',
            'sq': 'File type is not allowed',
            'de': 'Diesen Dateityp kannst Du nicht verwenden'
        },
        'profile.mediaImage.photo.required': {
            'en': 'profile.mediaImage.photo.required',
            'sq': 'Image file is required',
            'de': 'Bitte lade ein Bild hoch'
        },
        'organization.visitors.onlyDigits {min} {max}': {
            'en': 'organization.visitors.onlyDigits {min} {max}',
            'sq': 'Please enter only digits avoid using , . and the number should be less than {max} grater than {min}',
            'de': 'Bitte gib nur Ziffern zwischen {min} und {max} ein'
        },
        'organization.stage.onlyDigits {min} {max} {example}': {
            'en': 'organization.stage.onlyDigits {min} {max} {example}',
            'sq': 'Please enter a correct currency format, ex. {example} less than {max} grater than {min}',
            'de': 'Bitte gib folgendes Format ein: {example} zwischen {min} und {max}'
        },
        'organization.stage.new': {
            'en': 'organization.stage.new',
            'sq': 'new stage',
            'de': 'organization.stage.new'
        },
        'organization.stage.max.is': {
            'en': 'organization.stage.max.is',
            'sq': 'stage max limit',
            'de': 'organization.stage.max.is'
        },
        'content.pasted.above.limit': {
            'en': 'content.pasted.above.limit',
            'sq': 'Content can not be pasted because it is above the allowed limit',
            'de': 'Content can not be pasted because it is above the allowed limit'
        }
    };