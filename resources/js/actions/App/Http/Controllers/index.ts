import DashboardController from './DashboardController'
import CustomerController from './CustomerController'
import ItemController from './ItemController'
import InvoiceController from './InvoiceController'
import Settings from './Settings'
import UserPreferenceController from './UserPreferenceController'

const Controllers = {
    DashboardController: Object.assign(DashboardController, DashboardController),
    CustomerController: Object.assign(CustomerController, CustomerController),
    ItemController: Object.assign(ItemController, ItemController),
    InvoiceController: Object.assign(InvoiceController, InvoiceController),
    Settings: Object.assign(Settings, Settings),
    UserPreferenceController: Object.assign(UserPreferenceController, UserPreferenceController),
}

export default Controllers