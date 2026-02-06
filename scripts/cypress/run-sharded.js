#!/usr/bin/env node
"use strict";

const fs = require("fs");
const path = require("path");
const { spawn } = require("child_process");

const DEFAULT_SPEC_ROOT = "tests/cypress/integration/features";
const args = process.argv.slice(2);

const dryRunIndex = args.indexOf("--dry-run");
const dryRun = dryRunIndex !== -1;
if (dryRun) {
  args.splice(dryRunIndex, 1);
}

let specRoot = process.env.SPEC_ROOT || DEFAULT_SPEC_ROOT;
const specRootArgIndex = args.findIndex((arg) => arg.startsWith("--spec-root="));
if (specRootArgIndex !== -1) {
  const argValue = args[specRootArgIndex].split("=")[1];
  if (argValue) {
    specRoot = argValue;
  }
  args.splice(specRootArgIndex, 1);
}

const shardIndex = Number.parseInt(process.env.SHARD_INDEX || "0", 10);
const shardTotal = Number.parseInt(process.env.SHARD_TOTAL || "1", 10);

if (!Number.isInteger(shardIndex) || !Number.isInteger(shardTotal)) {
  console.error("SHARD_INDEX and SHARD_TOTAL must be integers.");
  process.exit(1);
}
if (shardTotal <= 0 || shardIndex < 0 || shardIndex >= shardTotal) {
  console.error("Invalid shard values. Ensure 0 <= SHARD_INDEX < SHARD_TOTAL.");
  process.exit(1);
}

function collectSpecs(rootDir) {
  const results = [];

  function walk(dir) {
    const entries = fs.readdirSync(dir, { withFileTypes: true });
    entries.forEach((entry) => {
      const fullPath = path.join(dir, entry.name);
      if (entry.isDirectory()) {
        walk(fullPath);
        return;
      }
      if (entry.isFile() && /\.features?$/.test(entry.name)) {
        results.push(fullPath);
      }
    });
  }

  if (!fs.existsSync(rootDir)) {
    console.error(`Spec root not found: ${rootDir}`);
    process.exit(1);
  }

  walk(rootDir);
  return results.sort();
}

const specs = collectSpecs(specRoot);
const shardSpecs = specs.filter((_, index) => index % shardTotal === shardIndex);

if (shardSpecs.length === 0) {
  console.error("No specs found for this shard.");
  process.exit(1);
}

const cypressArgs = ["run", "--spec", shardSpecs.join(",")];

const hasTags = args.some((arg) => arg.includes("TAGS="));
if (process.env.CYPRESS_TAGS && !hasTags) {
  cypressArgs.push("--env", `TAGS=${process.env.CYPRESS_TAGS}`);
}

cypressArgs.push(...args);

if (dryRun) {
  console.log(`Shard ${shardIndex}/${shardTotal} (${shardSpecs.length} specs)`);
  shardSpecs.forEach((spec) => console.log(spec));
  process.exit(0);
}

const localBin = path.join(process.cwd(), "node_modules", ".bin", "cypress");
const cypressBin = fs.existsSync(localBin) ? localBin : "cypress";

const child = spawn(cypressBin, cypressArgs, { stdio: "inherit" });
child.on("exit", (code) => process.exit(code ?? 1));
