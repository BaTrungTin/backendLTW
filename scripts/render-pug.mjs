import fs from 'fs';
import path from 'path';
import pug from 'pug';

const pugFile = process.argv[2];
const jsonFile = process.argv[3];

const data = JSON.parse(
  fs.readFileSync(jsonFile, 'utf8')
);

process.stdout.write(
  pug.renderFile(pugFile, {
    ...data,
    pathAdmin: data.pathAdmin ?? 'admin',
    basedir: path.dirname(pugFile),
  })
);