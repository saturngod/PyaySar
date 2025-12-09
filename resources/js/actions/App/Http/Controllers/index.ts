import CustomerController from './CustomerController'
import InvoiceController from './InvoiceController'
import Settings from './Settings'
import UserPreferenceController from './UserPreferenceController'

const Controllers = {
    CustomerController: Object.assign(CustomerController, CustomerController),
    InvoiceController: Object.assign(InvoiceController, InvoiceController),
    Settings: Object.assign(Settings, Settings),
    UserPreferenceController: Object.assign(UserPreferenceController, UserPreferenceController),
}

export default Controllers