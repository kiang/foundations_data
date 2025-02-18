# Taiwan Foundations Registry Data

This project collects and presents registration data of foundations (財團法人) in Taiwan from the Judicial Yuan (司法院). The data is accessible through a user-friendly interface at [foundations.olc.tw](https://foundations.olc.tw/).

## Overview

This system tracks registered foundations across all jurisdictions in Taiwan, including:
- 20 District Courts in Taiwan proper
- Kinmen District Court (金門地方法院)
- Lienchiang District Court (連江地方法院)

## Data Source

The data is sourced from the Judicial Yuan's Administrative Office Management Platform (司法院行政事務管理系統), specifically from:
`https://aomp109.judicial.gov.tw/judbp/`

## Features

- Complete registry information of foundations across Taiwan
- Foundation details including:
  - Registration number
  - Foundation name
  - Registration date
  - Purpose
  - Office locations
  - Board members and their positions
- Historical data tracking
- Search functionality
- Open data access

## Technical Implementation

The project consists of:
1. Data Collection Scripts (PHP)
   - Fetches raw CSV data from the Judicial Yuan
   - Processes and organizes data by court jurisdiction and year
   - Maintains data structure for main records, office locations, and member information

2. Web Interface
   - Provides public access to foundation information
   - Enables searching and browsing of foundation data
   - Available at [foundations.olc.tw](https://foundations.olc.tw/)

## Data Structure

The data is organized into three main categories:
1. Main Records (主檔)
   - Basic foundation information
2. Office Locations (事務所)
   - Registered addresses of foundation offices
3. Member Information (成員)
   - Details of board members and their positions

## Contributing

Contributions to improve the project are welcome. Please feel free to submit issues or pull requests.

## License

This project is dual-licensed:

### Software License (MIT)
The software code in this repository is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

### Data License (CC BY 4.0)
The foundation registry data obtained from the Judicial Yuan of Taiwan is available under the [Creative Commons Attribution 4.0 International License](https://creativecommons.org/licenses/by/4.0/).

When using this data, please provide appropriate attribution to the Judicial Yuan of Taiwan (司法院).

## Acknowledgments

- Judicial Yuan of Taiwan for providing the source data