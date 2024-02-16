import UIKit

struct Customer {
    let firstName: String
    let lastName: String
    let contactInfo: String // phone number or email
    let service: String
    let estimatedWaitTime: TimeInterval // in seconds
}

class QueueViewController: UIViewController, UITableViewDelegate, UITableViewDataSource {
    
    @IBOutlet weak var tableView: UITableView!
    
    var queue: [Customer] = []
    
    override func viewDidLoad() {
        super.viewDidLoad()
        // Do any additional setup after loading the view.
        tableView.delegate = self
        tableView.dataSource = self
    }
    
    func tableView(_ tableView: UITableView, numberOfRowsInSection section: Int) -> Int {
        return queue.count
    }
    
    func tableView(_ tableView: UITableView, cellForRowAt indexPath: IndexPath) -> UITableViewCell {
        let cell = tableView.dequeueReusableCell(withIdentifier: "CustomerCell", for: indexPath)
        
        let customer = queue[indexPath.row]
        cell.textLabel?.text = "\(customer.firstName) \(customer.lastName)"
        cell.detailTextLabel?.text = "Service: \(customer.service) | Estimated Wait Time: \(formatTime(customer.estimatedWaitTime))"
        
        return cell
    }
    
    func formatTime(_ timeInterval: TimeInterval) -> String {
        let formatter = DateComponentsFormatter()
        formatter.unitsStyle = .full
        formatter.allowedUnits = [.hour, .minute]
        return formatter.string(from: timeInterval) ?? ""
    }
    
    func checkIn(firstName: String, lastName: String, contactInfo: String, service: String) {
        let newCustomer = Customer(firstName: firstName, lastName: lastName, contactInfo: contactInfo, service: service, estimatedWaitTime: 1800) // 30 minutes in seconds
        queue.append(newCustomer)
        tableView.reloadData()
    }
    
    // You can call this function when a customer presses the check in button
    func customerPressedCheckInButton() {
        // For demonstration purposes, let's assume these values are provided by the user through text fields or some UI elements
        let firstName = "John"
        let lastName = "Doe"
        let contactInfo = "john.doe@example.com" // or phone number
        let service = "Manicure" // or any other service
        
        checkIn(firstName: firstName, lastName: lastName, contactInfo: contactInfo, service: service)
    }
}
