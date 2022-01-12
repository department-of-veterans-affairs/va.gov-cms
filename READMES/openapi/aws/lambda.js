const https = require('https');

function getRequest(path, queryParams) {
  const options = {
    hostname: 'content-api-poc-trbtppyqohmbnfdh8faiwyiic9hby79m.demo.cms.va.gov',
    port: 443,
    path: `/jsonapi${path}`,
    query: queryParams,
    method: 'GET',
    rejectUnauthorized: false,
    requestCert: true,
  };

  console.log(options);

  return new Promise((resolve, reject) => {
    const req = https.request(options, res => {

      if (res.statusCode != 200) {
        const error = new Error(`StatusCode: ${res.statusCode}`);
        error.code = res.statusCode;
        reject(error);
      }
      let rawData = '';


      let i = 2;
      res.on('data', chunk => {
        i++;
        rawData += chunk;
      });

      res.on('end', () => {
        try {
          resolve(JSON.parse(rawData));
        } catch (err) {
          console.log(err);
          reject(new Error(err));
        }
      });
    });

    req.on('error', err => {
      reject(new Error(err));
    });

    req.end();
  });
}

exports.handler = async event => {
  try {
    console.log('event');
    console.log(event);
    const result = await getRequest(event.path, event.queryStringParameters);

    // 👇️️ response structure assume you use proxy integration with API gateway
    return {
      statusCode: 200,
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify(result),
    };
  } catch (error) {
    console.log('Error is: 👉️',JSON.stringify( error));
    return {
      statusCode: error.code,
      body: {
        errorMessage: error.mesage
      },
    };
  }
};
