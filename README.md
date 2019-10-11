# DCP API
## License
Copyright (c) 2019 Qualcomm Technologies, Inc.

All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted (subject to the limitations in the disclaimer below) provided that the following conditions are met:

* Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
* Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
* Neither the name of Qualcomm Technologies, Inc. nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.
* The origin of this software must not be misrepresented; you must not claim that you wrote the original software. If you use this software in a product, an acknowledgment is required by displaying the trademark/log as per the details provided here: https://www.qualcomm.com/documents/dirbs-logo-and-brand-guidelines
* Altered source versions must be plainly marked as such, and must not be misrepresented as being the original software.
* This notice may not be removed or altered from any source distribution.

NO EXPRESS OR IMPLIED LICENSES TO ANY PARTY'S PATENT RIGHTS ARE GRANTED BY THIS LICENSE. THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.


## Quick Setup

## Pre-requisites
- PHP v7.1 or greater
- PostgresSQL v 11.4 or greater
- Composer dependency manager v1.8.4 or greater

### Project Setup
- Clone or fork the project in your local system `git clone https://github.com/CACF/DCP_API.git`
- Install all dependencies by running the following command in project root directory
`composer install`
- Rename `.env.example` to `.env`  by running the following command
`mv .env.example .env`
- Configure DB settings in your `.env` file
- Run `php artisan key:generate` to generate application key.
- Run `php artisan jwt:secret` to generate JWT secret key.
- Define `APP_URL` in `config/app.php` to your frontend application URL.
- Run DB migrations by `php artisan migrate`
- Congrats, you're all set up & ready to go.

### Optional Steps
- Run DB migrations with sample seeders `php artisan migrate --seed` or `php artisan migrate:refresh --seed`
- Run `php artisan setup:api {--wco}`

### WCO GSMA Configurations
In order to configure WCO GSMA Database with your instance, you need to have the following set of variables.
- `WCO_API_URL`,
- `WCO_ENCRYPTION_KEY`,
- `WCO_AUTH_TOKEN`,
- `WCO_AUTH_PASSWORD`,
- `WCO_ORGANIZATION_ID`

You can define these variables in `config/app.php`

Moreover, to get data from WCO GSMA API, you also need to set few more variables in your `.env` file.

- `WCO_PORT_NAME`,
- `WCO_COUNTRY`,
- `WCO_PORT_TYPE` 

### AWS S3 Bucket Configurations

In order to use AWS S3 bucket for your assets, you can configure the following variables  in your `.env` file:
- `IS_AWS` to `TRUE`,
- `AWS_KEY`,
- `AWS_SECRET`,
- `AWS_REGION`,
- `AWS_BUCKET`




