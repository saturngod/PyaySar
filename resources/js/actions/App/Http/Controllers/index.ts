import DashboardController from './DashboardController'
import CustomerController from './CustomerController'
import InvoiceController from './InvoiceController'
import AIController from './AIController'
import Settings from './Settings'
import UserPreferenceController from './UserPreferenceController'

const Controllers = {
    DashboardController: Object.assign(DashboardController, DashboardController),
    CustomerController: Object.assign(CustomerController, CustomerController),
    InvoiceController: Object.assign(InvoiceController, InvoiceController),
    AIController: Object.assign(AIController, AIController),
    Settings: Object.assign(Settings, Settings),
    UserPreferenceController: Object.assign(UserPreferenceController, UserPreferenceController),
}

export default Controllers