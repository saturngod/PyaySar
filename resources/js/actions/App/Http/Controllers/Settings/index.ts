import ProfileController from './ProfileController'
import PasswordController from './PasswordController'
import TwoFactorAuthenticationController from './TwoFactorAuthenticationController'
import InvoiceSettingsController from './InvoiceSettingsController'
import ApiTokenController from './ApiTokenController'

const Settings = {
    ProfileController: Object.assign(ProfileController, ProfileController),
    PasswordController: Object.assign(PasswordController, PasswordController),
    TwoFactorAuthenticationController: Object.assign(TwoFactorAuthenticationController, TwoFactorAuthenticationController),
    InvoiceSettingsController: Object.assign(InvoiceSettingsController, InvoiceSettingsController),
    ApiTokenController: Object.assign(ApiTokenController, ApiTokenController),
}

export default Settings