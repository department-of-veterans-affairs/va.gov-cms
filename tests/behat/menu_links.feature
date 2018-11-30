@api
Feature: Menus
Ensure all menu links are present

@spec @menus
  Scenario Outline: Main Menu items
    Then the following items should exist "<item>"

    Examples:
      | item                                                          |
      | VA Benefits and Health Care                                   |
      | Health Care                                                   |
      | View All in Health Care                                       |
      | About VA Health Benefits                                      |
      | How to Apply                                                  |
      | Family and Caregiver Health Benefits                          |
      | Apply Now for Health Care                                     |
      | Refill and Track Your Prescriptions                           |
      | Send a Secure Message to Your Health Care Team                |
      | Schedule and View VA Appointments                             |
      | View Your Lab and Test Results                                |
      | Order Hearing Aid Batteries and Prosthetic Socks              |
      | Disability                                                    |
      | View All in Disability                                        |
      | Eligibility                                                   |
      | How to File a Claim                                           |
      | Survivor and Dependent Compensation (DIC)                     |
      | Check Your Claim or Appeal Status                             |
      | View Your VA Payment History                                  |
      | Upload Evidence to Support Your Claim                         |
      | File for a VA Disability Increase                             |
      | File an Appeal                                                |
      | Education and Training                                        |
      | View All in Education                                         |
      | About GI Bill benefits                                        |
      | Eligibility                                                   |
      | How to Apply                                                  |
      | Vocational Rehabilitation and Employment                      |
      | Survivor and Dependent Education Benefits                     |
      | View Your VA Payment History                                  |
      | Check Your Post-9/11 GI Bill Benefits                         |
      | Transfer Your Post-9/11 GI Bill Benefits                      |
      | Change Your GI Bill School or Program                         |
      | Change Your Direct Deposit Information                        |
      | Careers and Employment                                        |
      | View All in Careers and Employment                            |
      | About Vocational Rehabilitation and Employment                |
      | How to Apply                                                  |
      | Veteran-Owned Small Business Support                          |
      | VA Transition Assistance                                      |
      | CareerScope Assessment                                        |
      | Find a Job                                                    |
      | Find VA Careers and Support                                   |
      | Print Your Civil Service Preference Letter                    |
      | Pension                                                       |
      | View All in Pension                                           |
      | Veterans Pension Eligibility                                  |
      | How to Apply                                                  |
      | Aid and Attendance Benefits and Household Allowance           |
      | Survivors Pension                                             |
      | Apply Now for a Veterans Pension                              |
      | Check Your Claim or Appeal Status                             |
      | View Your VA Payment History                                  |
      | Change Your Direct Deposit and Contact Information            |
      | Housing Assistance                                            |
      | View All in Housing Assistance                                |
      | About VA Home Loan Types                                      |
      | Check Your Appeal Status                                      |
      | How to Apply for Your COE                                     |
      | About Disability Housing Grants                               |
      | Check Your SAH Claim Status                                   |
      | How to Apply for an SAH Grant                                 |
      | Life Insurance                                                |
      | View All in Life Insurance                                    |
      | About Life Insurance Options                                  |
      | Benefits for Totally Disabled or Terminally Ill Policyholders |
      | Beneficiary Financial Counseling and Online Will Preparation  |
      | Manage Your Policy                                            |
      | Update Your Beneficiaries                                     |
      | File a Claim for Insurance Benefits                           |
      | Check Your Appeal Status                                      |
      | Burials and Memorials                                         |
      | View All Burials and Memorials                                |
      | Eligibility                                                   |
      | Pre-need Burial Eligibility Determination                     |
      | Veteran Burial Allowance                                      |
      | Memorial Items                                                |
      | Survivor and Dependent Compensation (DIC)                     |
      | Plan a Burial for a Family Member                             |
      | Schedule a Burial in a VA National Cemetery                   |
      | Find a Cemetery                                               |
      | Request Military Records (DD214)                              |
      | Records                                                       |
      | View All in Records                                           |
      | Get Your VA Medical Records (Blue Button)                     |
      | Download Your VA Benefit Letters                              |
      | Learn How to Apply for a Home Loan COE                        |
      | Get Veteran ID Cards                                          |
      | Request Military Records (DD214)                              |
      | How to Apply for a Discharge Upgrade                          |
      | View Your VA Payment History                                  |
      | Search Historical Military Records (National Archives)        |
      | About VA                                                      |
      | Veterans Health Administration                                |
      | Veterans Benefits Administration                              |
      | National Cemetery Administration                              |
      | VA Leadership                                                 |
      | Public Affairs                                                |
      | Congressional Affairs                                         |
      | All VA Offices and Organizations                              |
      | Health Research                                               |
      | Public Health                                                 |
      | Veterans Choice Program                                       |
      | VA Open Data                                                  |
      | Veterans Analysis and Statistics                              |
      | Appeals Modernization                                         |
      | VA Center for Innovation                                      |
      | Recovery Act                                                  |
      | VA Mission, Vision, and Values                                |
      | History of VA                                                 |
      | VA Plans, Budget, and Performance                             |
      | National Cemetery History Program                             |
      | Veterans Legacy Program                                       |
      | Volunteer or Donate                                           |
      | Find a VA Location                                            |
