import mongoose from 'mongoose'
import { getdata } from './api.js'
import readline from 'readline'

const { Schema, model } = mongoose

// Define schemas
const objectagreationsSchema = new mongoose.Schema(
  {
    course_id: {
      type: String,
      required: true,
      index: true
    },
    title: {
      type: String,
      required: true
    },
    dept_name: {
      type: String,
      required: true
    },
    credits: {
      type: Number,
      required: true
    },
    ID: {
      type: String,
      required: true
    },
    sec_id: {
      type: String,
      required: true
    },
    semester: {
      type: String,
      required: true,
      enum: ['Fall', 'Spring', 'Summer', 'Winter']
    },
    year: {
      type: Number,
      required: true
    }
  },
  {
    collection: 'objectagreations',
    timestamps: false,
    versionKey: false
  }
)

const objectbasicsSchema = new mongoose.Schema(
  {
    ID: {
      type: String,
      required: true,
      index: true
    },
    dept_name: {
      type: String,
      required: true
    },
    salary: {
      type: Number,
      required: true
    }
  },
  {
    collection: 'objectbasics',
    timestamps: false,
    versionKey: false
  }
)

// Create models
const ObjectAgreation = mongoose.model('ObjectAgreation', objectagreationsSchema)
const ObjectBasic = mongoose.model('ObjectBasic', objectbasicsSchema)

// MongoDB connection settings
const uri = 'mongodb://127.0.0.1:27017/Temario1'
const options = {
  autoIndex: true,
  maxPoolSize: 10,
  serverSelectionTimeoutMS: 5000,
  socketTimeoutMS: 45000,
  family: 4
}

// Create readline interface
const rl = readline.createInterface({
  input: process.stdin,
  output: process.stdout
})

async function handleObjectAgreations() {
  try {
    await mongoose.connect(uri, options)
    console.log('Connected to MongoDB - Database: Temario1')

    await ObjectAgreation.deleteMany({})
    console.log('Cleared existing objectagreations data')

    const enrollmentData = await getdata()
    if (!enrollmentData || !enrollmentData.enrollments) {
      throw new Error('Invalid data structure received from API')
    }

    const formattedData = enrollmentData.enrollments.map(item => ({
      course_id: item.course_info.course_id,
      title: item.course_info.title,
      dept_name: item.course_info.dept_name,
      credits: Number(item.course_info.credits),
      ID: item.course_info.ID,
      sec_id: item.course_info.sec_id,
      semester: item.course_info.semester,
      year: Number(item.course_info.year)
    }))

    const results = await ObjectAgreation.insertMany(formattedData)
    console.log(`\nStored ${results.length} records in objectagreations`)

    const savedData = await ObjectAgreation.find({}).lean()
    console.log('\nStored Documents:')
    console.log(JSON.stringify(savedData, null, 2))
  } catch (error) {
    console.error('Error:', error.message)
  } finally {
    await mongoose.connection.close()
    showMenu()
  }
}

async function handleObjectBasics() {
  try {
    await mongoose.connect(uri, options)
    console.log('Connected to MongoDB - Database: Temario1')

    await ObjectBasic.deleteMany({})
    console.log('Cleared existing objectbasics data')

    const instructorData = await getdata()
    if (!instructorData || !instructorData.instructors) {
      throw new Error('Invalid instructor data structure')
    }

    const formattedData = instructorData.instructors.map(item => ({
      ID: item.instructor_info.ID,
      dept_name: item.instructor_info.dept_name,
      salary: Number(item.instructor_info.salary)
    }))

    const results = await ObjectBasic.insertMany(formattedData)
    console.log(`\nStored ${results.length} records in objectbasics`)

    const savedData = await ObjectBasic.find({}).lean()
    console.log('\nStored Documents:')
    console.log(JSON.stringify(savedData, null, 2))
  } catch (error) {
    console.error('Error:', error.message)
  } finally {
    await mongoose.connection.close()
    showMenu()
  }
}

function showMenu() {
  console.log('\n=== MongoDB Schema Manager ===')
  console.log('1. Work with Object Agreations')
  console.log('2. Work with Object Basics')
  console.log('3. Exit')

  rl.question('Select an option (1-3): ', async choice => {
    switch (choice) {
      case '1':
        await handleObjectAgreations()
        break
      case '2':
        await handleObjectBasics()
        break
      case '3':
        rl.close()
        process.exit(0)
        break
      default:
        console.log('Invalid option, please try again')
        showMenu()
    }
  })
}

// Start the application
showMenu()
